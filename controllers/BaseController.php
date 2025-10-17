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
        try {
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
        } catch (mysqli_sql_exception $e) {
            // Log the SQL error for debugging but do not throw to the user.
            error_log('DB error in fetchAll: ' . $e->getMessage() . ' -- SQL: ' . $sql);
            return [];
        }
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

    /**
     * Execute a write query (INSERT/UPDATE/DELETE) with optional prepared params.
     * If $returnAffected is true return affected_rows (int), otherwise return bool success.
     */
    protected static function execute(string $sql, string $types = '', array $params = [], bool $returnAffected = false) {
        $mysqli = self::db();
        try {
            if ($types !== '' && !empty($params)) {
                $stmt = $mysqli->prepare($sql);
                if (!$stmt) return $returnAffected ? 0 : false;
                $stmt->bind_param($types, ...$params);
                $ok = $stmt->execute();
                $affected = $stmt->affected_rows;
                $stmt->close();
                return $returnAffected ? $affected : ($ok === true || $affected >= 0);
            }
            $res = $mysqli->query($sql);
            if ($returnAffected) return $mysqli->affected_rows;
            return $res !== false;
        } catch (mysqli_sql_exception $e) {
            error_log('DB error in execute: ' . $e->getMessage() . ' -- SQL: ' . $sql);
            return $returnAffected ? 0 : false;
        }
    }
}
