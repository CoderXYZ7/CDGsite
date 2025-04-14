<?php
class Admin {
    private $db;
    
    public function __construct(Database $db) {
        $this->db = $db;
    }
    
    public function generateActivationCode($adminUserId) {
        // Verify admin privileges
        if (!$this->isAdmin($adminUserId)) {
            throw new Exception("Unauthorized");
        }
        
        // Generate unique code
        $code = bin2hex(random_bytes(16));
        
        // Set expiration (24 hours)
        $expirationDate = date('Y-m-d H:i:s', time() + ACTIVATION_CODE_EXPIRE);
        
        // Store in database
        $this->db->query(
            "INSERT INTO ActivationCodes (Code, CreatedBy, ExpirationDate) 
             VALUES (?, ?, ?)",
            [$code, $adminUserId, $expirationDate]
        );
        
        return [
            'code' => $code,
            'expiration' => $expirationDate
        ];
    }
    
    public function listActivationCodes($adminUserId) {
        if (!$this->isAdmin($adminUserId)) {
            throw new Exception("Unauthorized");
        }
        
        return $this->db->query(
            "SELECT c.CodeID, c.Code, c.CreationDate, c.ExpirationDate, 
                    c.UsedBy, c.UsedDate, u1.Username AS CreatedBy,
                    u2.Username AS UsedByUsername
             FROM ActivationCodes c
             JOIN Users u1 ON c.CreatedBy = u1.UserID
             LEFT JOIN Users u2 ON c.UsedBy = u2.UserID
             ORDER BY c.CreationDate DESC"
        )->fetchAll();
    }
    
    public function revokeActivationCode($adminUserId, $code) {
        if (!$this->isAdmin($adminUserId)) {
            throw new Exception("Unauthorized");
        }
        
        $result = $this->db->query(
            "DELETE FROM ActivationCodes WHERE Code = ? AND UsedBy IS NULL",
            [$code]
        );
        
        if ($result->rowCount() === 0) {
            throw new Exception("Code not found or already used");
        }
        
        return true;
    }
    
    private function isAdmin($userId) {
        $rank = $this->db->query(
            "SELECT RankLevel FROM Users u 
             JOIN Ranks r ON u.RankID = r.RankID 
             WHERE u.UserID = ?",
            [$userId]
        )->fetchColumn();
        
        return $rank >= 3; // Assuming 3 is admin level
    }
}