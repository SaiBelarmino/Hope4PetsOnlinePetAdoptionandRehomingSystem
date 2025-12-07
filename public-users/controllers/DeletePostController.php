<?php
// DeletePostController.php
// Handles deletion of a post by the owner

if (session_status() === PHP_SESSION_NONE) {
	session_start();
}


require_once __DIR__ . '/../../config/db-connection/db_connection.php';
require_once __DIR__ . '/../../config/SessionManager.php';

function delete_post($postId, $userId) {
	global $conn;
	// Check if the post exists and belongs to the user
	$stmt = $conn->prepare('SELECT id FROM posts WHERE id = ? AND user_id = ?');
	$stmt->bind_param('ii', $postId, $userId);
	$stmt->execute();
	$result = $stmt->get_result();
	$post = $result->fetch_assoc();
	$stmt->close();
	if (!$post) {
		return false;
	}
	// Delete post photos
	$stmt = $conn->prepare('DELETE FROM post_photos WHERE post_id = ?');
	$stmt->bind_param('i', $postId);
	$stmt->execute();
	$stmt->close();
	// Delete post videos
	$stmt = $conn->prepare('DELETE FROM post_videos WHERE post_id = ?');
	$stmt->bind_param('i', $postId);
	$stmt->execute();
	$stmt->close();
	// Delete post comments
	$stmt = $conn->prepare('DELETE FROM post_comments WHERE post_id = ?');
	$stmt->bind_param('i', $postId);
	$stmt->execute();
	$stmt->close();
	// Delete post reactions
	$stmt = $conn->prepare('DELETE FROM post_reactions WHERE post_id = ?');
	$stmt->bind_param('i', $postId);
	$stmt->execute();
	$stmt->close();
	// Delete the post itself
	$stmt = $conn->prepare('DELETE FROM posts WHERE id = ?');
	$stmt->bind_param('i', $postId);
	$stmt->execute();
	$stmt->close();
	return true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post_id'])) {
	$postId = (int)$_POST['post_id'];
	$userId = \SessionManager::getUserId();
	if (!$userId) {
		\SessionManager::setFlash('error', 'You must be logged in to delete a post.');
		header('Location: ../views/index.php');
		exit;
	}
	if (delete_post($postId, $userId)) {
		\SessionManager::setFlash('success', 'Post deleted successfully.');
	} else {
		\SessionManager::setFlash('error', 'Failed to delete post.');
	}
	header('Location: ../views/index.php');
	exit;
} else {
	// Invalid request
	header('Location: ../views/index.php');
	exit;
}

