<?php
class Auth {
    private $db;
    
    public function __construct(Database $db) {
        $this->db = $db;
    }
    
    public function register($username, $email, $password, $activationCode) {
        // Validate activation code
        $codeData = $this->db->query(
            "SELECT * FROM ActivationCodes WHERE Code = ? AND UsedBy IS NULL AND ExpirationDate > NOW()",
            [$activationCode]
        )->fetch();
        
        if (!$codeData) {
            throw new Exception("Invalid or expired activation code");
        }
        
        // Check if username or email exists
        $userExists = $this->db->query(
            "SELECT UserID FROM Users WHERE Username = ? OR Email = ?",
            [$username, $email]
        )->fetch();
        
        if ($userExists) {
            throw new Exception("Username or email already exists");
        }
        
        // Hash password
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        
        // Create user (default rank is 1 - User)
        $this->db->query(
            "INSERT INTO Users (Username, Email, PasswordHash, RankID) VALUES (?, ?, ?, 1)",
            [$username, $email, $passwordHash]
        );
        
        $userId = $this->db->lastInsertId();
        
        // Mark activation code as used
        $this->db->query(
            "UPDATE ActivationCodes SET UsedBy = ?, UsedDate = NOW() WHERE CodeID = ?",
            [$userId, $codeData['CodeID']]
        );
        
        return $userId;
    }
    
    public function login($username, $password, $ip, $userAgent) {
        // Get user
        $user = $this->db->query(
            "SELECT UserID, Username, PasswordHash, Status FROM Users WHERE Username = ?",
            [$username]
        )->fetch();
        
        if (!$user || !password_verify($password, $user['PasswordHash'])) {
            throw new Exception("Invalid username or password");
        }
        
        if ($user['Status'] !== 'active') {
            throw new Exception("Account is " . $user['Status']);
        }
        
        // Generate JWT token
        $token = $this->generateToken($user['UserID']);
        
        // Store session
        $this->createSession($user['UserID'], $token['token'], $token['expires'], $ip, $userAgent);
        
        // Update last login
        $this->db->query(
            "UPDATE Users SET LastLogin = NOW() WHERE UserID = ?",
            [$user['UserID']]
        );
        
        return $token;
    }
    
    private function generateToken($userId) {
        $issuedAt = time();
        $expirationTime = $issuedAt + JWT_EXPIRE;
        
        $payload = [
            'iat' => $issuedAt,
            'exp' => $expirationTime,
            'sub' => $userId
        ];
        
        $token = JWT::encode($payload, JWT_SECRET, 'HS256');
        
        return [
            'token' => $token,
            'expires' => date('Y-m-d H:i:s', $expirationTime)
        ];
    }
    
    private function createSession($userId, $token, $expires, $ip, $userAgent) {
        $this->db->query(
            "INSERT INTO Sessions (UserID, Token, ExpirationDate, IP, UserAgent) 
             VALUES (?, ?, ?, ?, ?)",
            [$userId, $token, $expires, $ip, $userAgent]
        );
    }
    
    public function validateToken($token) {
        try {
            $decoded = JWT::decode($token, new Key(JWT_SECRET, 'HS256'));
            return (array)$decoded;
        } catch (Exception $e) {
            return false;
        }
    }
    
    public function logout($token) {
        // Delete session
        $this->db->query(
            "DELETE FROM Sessions WHERE Token = ?",
            [$token]
        );
        
        return true;
    }
}