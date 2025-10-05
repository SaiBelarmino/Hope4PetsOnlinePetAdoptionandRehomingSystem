<?php
require_once __DIR__ . '/../../controllers/BaseController.php';

class PublicAuthenticationSignupController extends BaseController {
    /**
     * Register a new user according to users table schema.
     * Expects keys: full_name, email, password, (optional) birthday (Y-m-d), gender
     */
    public static function register(array $data, array &$errors = []): bool {
        $mysqli = self::db();

        // Basic validation
        $fullName = trim($data['full_name'] ?? '');
        $email = strtolower(trim($data['email'] ?? ''));
        $password = $data['password'] ?? '';
        $birthday = $data['birthday'] ?? null; // optional
        $gender = $data['gender'] ?? 'unspecified';

        if ($fullName === '') $errors[] = 'Full name is required.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
        if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
        if ($gender && !in_array($gender, ['male','female','other','unspecified'], true)) {
            $errors[] = 'Invalid gender value.';
        }
        if ($birthday) {
            $d = DateTime::createFromFormat('Y-m-d', $birthday);
            if (!$d || $d->format('Y-m-d') !== $birthday) {
                $errors[] = 'Birthday must be in Y-m-d format.';
                $birthday = null; // ignore invalid date
            }
        }

        if ($errors) return false;

        // Duplicate email check
        $existing = self::fetchValue('SELECT id FROM users WHERE email = ? LIMIT 1', 's', [$email]);
        if ($existing) {
            $errors[] = 'Email already registered.';
            return false;
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);

        $sql = 'INSERT INTO users (full_name, birthday, gender, email, password_hash, created_at, updated_at) VALUES (?,?,?,?,?,NOW(),NOW())';
        $stmt = $mysqli->prepare($sql);
        if (!$stmt) {
            $errors[] = 'Failed to prepare statement.';
            return false;
        }
        // birthday can be null -> use bind_param with appropriate types (s = string, null must be converted)
        // We'll bind as strings and allow null by setting to null and using param types. For mysqli, need to adjust if null.
        $stmt->bind_param('sssss', $fullName, $birthday, $gender, $email, $hash);
        $ok = $stmt->execute();
        if (!$ok) {
            // If unique constraint violation
            if ($mysqli->errno === 1062) {
                $errors[] = 'Email already registered.';
            } else {
                $errors[] = 'Database error: ' . $mysqli->error;
            }
        }
        $stmt->close();
        return $ok;
    }
}
?>