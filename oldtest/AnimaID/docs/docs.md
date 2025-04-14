# AnimaID Account System Design

I'll design a unified account system called AnimaID that meets your requirements. Here's a comprehensive approach:

## Core Components

### 1. Database Structure

### Users Table

- `UserID` (primary key)
- `Username`
- `Email`
- `PasswordHash`
- `CreationDate`
- `LastLogin`
- `Status` (active, suspended, etc.)
- `RankID` (foreign key)

### ActivationCodes Table

- `CodeID` (primary key)
- `Code` (unique)
- `CreatedBy` (admin UserID)
- `CreationDate`
- `ExpirationDate`
- `UsedBy` (nullable)
- `UsedDate` (nullable)

### Tags Table

- `TagID` (primary key)
- `TagName`
- `TagDescription`

### UserTags Table (Many-to-many relationship)

- `UserID` (foreign key)
- `TagID` (foreign key)

### Ranks Table

- `RankID` (primary key)
- `RankName`
- `RankLevel`
- `RankDescription`
- `Permissions` (JSON/serialized array)

### Sessions Table

- `SessionID` (primary key)
- `UserID` (foreign key)
- `Token`
- `CreationDate`
- `ExpirationDate`
- `LastActivity`
- `IP`
- `UserAgent`

### AuthConnections Table (For connecting to other systems)

- `ConnectionID` (primary key)
- `UserID` (foreign key)
- `SystemID`
- `ExternalID`
- `ConnectionDate`
- `LastUsed`

### 2. PHP API Endpoints

### Authentication Endpoints

- `POST /auth/register` - Register with activation code
- `POST /auth/login` - Login and generate token
- `POST /auth/logout` - Invalidate token
- `POST /auth/refresh` - Refresh token
- `GET /auth/verify` - Verify session token (for other systems)

### Activation Code Endpoints

- `POST /admin/codes/generate` - Generate activation code (admin only)
- `GET /admin/codes/list` - List activation codes (admin only)
- `DELETE /admin/codes/{code}` - Revoke activation code (admin only)

### User Management Endpoints

- `GET /users/me` - Get current user profile
- `PUT /users/me` - Update current user profile
- `GET /users/{id}` - Get user by ID (admin only)
- `PUT /users/{id}` - Update user (admin only)
- `DELETE /users/{id}` - Delete user (admin only)
- `GET /users/search` - Search users by criteria (admin only)

### Tags Endpoints

- `GET /tags` - List all tags
- `POST /tags` - Create new tag (admin only)
- `PUT /tags/{id}` - Update tag (admin only)
- `DELETE /tags/{id}` - Delete tag (admin only)
- `POST /users/{id}/tags` - Assign tag to user
- `DELETE /users/{id}/tags/{tagId}` - Remove tag from user

### Ranks Endpoints

- `GET /ranks` - List all ranks
- `POST /ranks` - Create new rank (admin only)
- `PUT /ranks/{id}` - Update rank (admin only)
- `DELETE /ranks/{id}` - Delete rank (admin only)
- `PUT /users/{id}/rank` - Change user rank (admin only)

### External Connection Endpoints

- `POST /connect/{systemId}` - Connect to external system
- `DELETE /connect/{systemId}` - Disconnect from external system
- `GET /connect` - List connected systems

## Key Features Implementation

### Activation Code System

```php
function generateActivationCode($adminUserId) {
    // Validate admin privileges
    if (!isAdmin($adminUserId)) {
        return ['error' => 'Unauthorized'];
    }
    
    // Generate unique code
    $code = bin2hex(random_bytes(16));
    
    // Store in database with expiration (24 hours)
    $expirationDate = date('Y-m-d H:i:s', strtotime('+24 hours'));
    
    // Insert into database
    $db->query("INSERT INTO activation_codes (code, created_by, creation_date, expiration_date)
                VALUES ('$code', $adminUserId, NOW(), '$expirationDate')");
    
    return ['code' => $code, 'expiration' => $expirationDate];
}
```

### External System Authentication

```php
function verifyUserForExternalSystem($token, $systemId) {
    // Validate token
    $session = validateToken($token);
    if (!$session) {
        return ['error' => 'Invalid token'];
    }
    
    $userId = $session['user_id'];
    
    // Check if user is allowed to access this system
    // This could be based on user tags, rank, or explicit permissions
    if (!canAccessSystem($userId, $systemId)) {
        return ['error' => 'Access denied'];
    }
    
    // Log the connection
    logSystemAccess($userId, $systemId);
    
    // Return user info needed by external system
    $userData = getUserBasicInfo($userId);
    $userTags = getUserTags($userId);
    $userRank = getUserRank($userId);
    
    return [
        'userId' => $userId,
        'username' => $userData['username'],
        'email' => $userData['email'],
        'tags' => $userTags,
        'rank' => $userRank,
        'timestamp' => time()
    ];
}
```

### Tag System

```php
function assignTagToUser($userId, $tagId, $adminId) {
    // Validate admin privileges
    if (!isAdmin($adminId)) {
        return ['error' => 'Unauthorized'];
    }
    
    // Check if user and tag exist
    if (!userExists($userId) || !tagExists($tagId)) {
        return ['error' => 'Invalid user or tag'];
    }
    
    // Check if tag is already assigned
    if (hasTag($userId, $tagId)) {
        return ['error' => 'Tag already assigned'];
    }
    
    // Assign tag
    $db->query("INSERT INTO user_tags (user_id, tag_id) VALUES ($userId, $tagId)");
    
    return ['success' => true];
}
```

## Security Considerations

- **Password Storage**: Use bcrypt or Argon2 for password hashing
- **Token Security**: Use JWT with short lifespans and refresh tokens
- **Rate Limiting**: Implement API rate limiting to prevent brute force
- **Input Validation**: Validate all inputs to prevent SQL injection
- **HTTPS**: Enforce HTTPS for all API communications
- **Logs**: Maintain detailed logs for security audits

## Integration with Other Systems

For other systems to integrate with AnimaID:

1. Implement the client-side authentication flow
2. Make API calls to AnimaID for user verification
3. Use the returned user data (including tags and rank) for system-specific permissions

**Example API client for your other systems:**

```php
class AnimaIDClient {
    private $apiUrl;
    private $systemId;
    private $systemSecret;
    
    public function __construct($apiUrl, $systemId, $systemSecret) {
        $this->apiUrl = $apiUrl;
        $this->systemId = $systemId;
        $this->systemSecret = $systemSecret;
    }
    
    public function verifyUser($token) {
        $response = $this->makeRequest('GET', '/auth/verify', [
            'token' => $token,
            'system_id' => $this->systemId
        ]);
        
        return $response;
    }
    
    private function makeRequest($method, $endpoint, $data = []) {
        // Implementation of HTTP request with proper authentication
    }
}
```
