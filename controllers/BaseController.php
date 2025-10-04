<?php
// Central shared base for all controllers
// Provides DB connection access and simple helper methods.

require_once __DIR__ . '/../config/db-connection/db_connection.php';

abstract class BaseController {
    protected static ?mysqli $conn = null;

    protected static function db(): mysqli {
        if (self::$conn === null) {
            // assumes $conn created in included db_connection.php
            global $conn;
            if (!$conn instanceof mysqli) {
                throw new RuntimeException('Database connection not initialized.');
            }
            self::$conn = $conn;
        }
        return self::$conn;
    }

    protected static function fetchAll(string $sql, string $types = '', array $params = []): array {
        $mysqli = self::db();
        if ($types !== '' && !empty($params)) {
            $stmt = $mysqli->prepare($sql);
            if (!$stmt) return [];
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
            $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
            $stmt->close();
            return $rows;
        }
        $result = $mysqli->query($sql);
        if (!$result) return [];
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        return $rows;
    }

    protected static function fetchOne(string $sql, string $types = '', array $params = []): ?array {
        $rows = self::fetchAll($sql, $types, $params);
        return $rows[0] ?? null;
    }

    protected static function fetchValue(string $sql, string $types = '', array $params = [], $default = null) {
        $row = self::fetchOne($sql, $types, $params);
        if ($row) {
            return array_shift($row);
        }
        return $default;
    }
}
