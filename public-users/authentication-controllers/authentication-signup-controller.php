<?php
// Public User Signup Controller (Refactored to use BaseController)
// Handles POST request from signup form and creates a new user record.

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	header('Location: ../user-authentication/authentication-signup.php');
	exit();
}

require_once __DIR__ . '/../../controllers/BaseController.php';

class PublicSignupController extends BaseController {
	public static function handle(): void {
		$fullName = isset($_POST['name']) ? trim($_POST['name']) : '';
		$email    = isset($_POST['email']) ? strtolower(trim($_POST['email'])) : '';
		$password = isset($_POST['password']) ? $_POST['password'] : '';

		$fullName = preg_replace('/\s+/', ' ', $fullName);
		$fullName = htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8');

		if ($fullName === '' || mb_strlen($fullName) > 150) {
			self::redirect(['error' => 'Invalid name provided.']);
		}
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			self::redirect(['error' => 'Invalid email address.']);
		}
		if (strlen($password) < 6) {
			self::redirect(['error' => 'Password must be at least 6 characters.']);
		}

		$mysqli = self::db();

		$check = $mysqli->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
		if (!$check) {
			self::redirect(['error' => 'Server error (prep1).']);
		}
		$check->bind_param('s', $email);
		$check->execute();
		$check->store_result();
		if ($check->num_rows > 0) {
			$check->close();
			self::redirect(['error' => 'Email already registered.']);
		}
		$check->close();

		$passwordHash = password_hash($password, PASSWORD_DEFAULT);
		if ($passwordHash === false) {
			self::redirect(['error' => 'Failed to process password.']);
		}

		$ins = $mysqli->prepare('INSERT INTO users (full_name, email, password_hash) VALUES (?, ?, ?)');
		if (!$ins) {
			self::redirect(['error' => 'Server error (prep2).']);
		}
		$ins->bind_param('sss', $fullName, $email, $passwordHash);
		if (!$ins->execute()) {
			$ins->close();
			self::redirect(['error' => 'Insert failed.']);
		}
		$ins->close();

		self::redirect(['success' => '1']);
	}

	private static function redirect(array $params): void {
		$base = '../user-authentication/authentication-signup.php';
		$query = http_build_query($params);
		header('Location: ' . $base . ($query ? ('?' . $query) : ''));
		exit();
	}
}

PublicSignupController::handle();
