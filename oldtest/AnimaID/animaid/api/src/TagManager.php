<?php
class TagManager {
    private $db;
    
    public function __construct(Database $db) {
        $this->db = $db;
    }
    
    public function createTag($name, $description, $adminUserId) {
        if (!$this->isAuthorized($adminUserId, 'manage_tags')) {
            throw new Exception("Unauthorized");
        }
        
        try {
            $this->db->query(
                "INSERT INTO Tags (TagName, TagDescription) VALUES (?, ?)",
                [$name, $description]
            );
            
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            if ($e->errorInfo[1] === 1062) { // Duplicate entry
                throw new Exception("Tag name already exists");
            }
            throw $e;
        }
    }
    
    public function assignTagToUser($userId, $tagId, $adminUserId) {
        if (!$this->isAuthorized($adminUserId, 'manage_tags')) {
            throw new Exception("Unauthorized");
        }
        
        // Check if user and tag exist
        if (!$this->userExists($userId) || !$this->tagExists($tagId)) {
            throw new Exception("User or tag does not exist");
        }
        
        try {
            $this->db->query(
                "INSERT INTO UserTags (UserID, TagID) VALUES (?, ?)",
                [$userId, $tagId]
            );
            
            return true;
        } catch (PDOException $e) {
            if ($e->errorInfo[1] === 1062) { // Duplicate entry
                throw new Exception("Tag already assigned to user");
            }
            throw $e;
        }
    }
    
    private function isAuthorized($userId, $permission) {
        $permissions = $this->db->query(
            "SELECT r.Permissions FROM Users u 
             JOIN Ranks r ON u.RankID = r.RankID 
             WHERE u.UserID = ?",
            [$userId]
        )->fetchColumn();
        
        $permissions = json_decode($permissions, true);
        return !empty($permissions[$permission]);
    }
    
    private function userExists($userId) {
        return (bool)$this->db->query(
            "SELECT 1 FROM Users WHERE UserID = ?",
            [$userId]
        )->fetchColumn();
    }
    
    private function tagExists($tagId) {
        return (bool)$this->db->query(
            "SELECT 1 FROM Tags WHERE TagID = ?",
            [$tagId]
        )->fetchColumn();
    }
}