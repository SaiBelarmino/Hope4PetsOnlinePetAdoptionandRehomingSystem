<?php
require_once __DIR__ . '/../../config/db-connection/db_connection.php';

class SearchController {
    public static function search($q) {
        $conn = get_db_connection();
        $results = [
            'users' => [],
            'pets' => [],
            'posts' => []
        ];

        // Search users (by name or email)
        $stmt = $conn->prepare("SELECT id, full_name, email, profile_photo FROM users WHERE full_name LIKE ? OR email LIKE ? LIMIT 10");
        $searchTerm = "%$q%";
        $stmt->bind_param('ss', $searchTerm, $searchTerm);
        $stmt->execute();
        $userRes = $stmt->get_result();
        while ($row = $userRes->fetch_assoc()) {
            $results['users'][] = $row;
        }
        $stmt->close();

        // Search pets (by name, breed, description)
        $stmt = $conn->prepare("SELECT id, name, breed, description FROM pets WHERE name LIKE ? OR breed LIKE ? OR description LIKE ? LIMIT 10");
        $stmt->bind_param('sss', $searchTerm, $searchTerm, $searchTerm);
        $stmt->execute();
        $petRes = $stmt->get_result();
        while ($row = $petRes->fetch_assoc()) {
            $results['pets'][] = $row;
        }
        $stmt->close();

        // Search posts (by content)
        $stmt = $conn->prepare("SELECT id, content FROM posts WHERE content LIKE ? LIMIT 10");
        $stmt->bind_param('s', $searchTerm);
        $stmt->execute();
        $postRes = $stmt->get_result();
        while ($row = $postRes->fetch_assoc()) {
            $results['posts'][] = $row;
        }
        $stmt->close();

        $conn->close();
        return $results;
    }
}
