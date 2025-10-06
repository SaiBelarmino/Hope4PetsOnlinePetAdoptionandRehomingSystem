<?php
// Public User Login Controller (Refactored to use BaseController)
// Processes login form submission, verifies credentials, starts session.

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	header('Location: ../user-authentication/authentication-login.php');
	exit();
}

require_once __DIR__ . '/../../controllers/BaseController.php';
session_start();

class PublicLoginController extends BaseController {
	public static function handle(): void {
		$email = isset($_POST['email']) ? strtolower(trim($_POST['email'])) : '';
		$password = isset($_POST['password']) ? $_POST['password'] : '';
		if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
			self::redirect(['error' => 'Invalid credentials.']);
		}

		$mysqli = self::db();
		$stmt = $mysqli->prepare('SELECT id, full_name, email, password_hash, is_verified FROM users WHERE email = ? LIMIT 1');
		if (!$stmt) {
			self::redirect(['error' => 'Server error.']);
		}
		$stmt->bind_param('s', $email);
		$stmt->execute();
		$result = $stmt->get_result();
		$user = $result->fetch_assoc();
		$stmt->close();

		if (!$user || !password_verify($password, $user['password_hash'])) {
			self::redirect(['error' => 'Invalid credentials.']);
		}

		session_regenerate_id(true);
		$_SESSION['user_id'] = (int)$user['id'];
		$_SESSION['user_name'] = $user['full_name'];
		$_SESSION['user_email'] = $user['email'];
		$_SESSION['user_verified'] = (int)$user['is_verified'];

		header('Location: ../views/index.php');
		exit();
	}

	private static function redirect(array $params): void {
		$base = '../user-authentication/authentication-login.php';
		$query = http_build_query($params);
		header('Location: ' . $base . ($query ? ('?' . $query) : ''));
		exit();
	}
}

PublicLoginController::handle();

