<?php
// public-users/api/get_post_media.php
// Returns all photo and video URLs for a given post as JSON

if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../config/db-connection/db_connection.php';

function resolve_media_path($path) {
    if (empty($path)) return '../../assets/images/placeholder.png';
    $p = trim($path);
    if (preg_match('#^https?://#i', $p)) return $p;
    $normalized = str_replace('\\', '/', $p);
    $pos = stripos($normalized, 'storage/');
    if ($pos !== false) {
        $sub = substr($normalized, $pos);
        return '../../' . ltrim($sub, '/');
    }
    $normalized = preg_replace('#^(\.{1,2}/)+#', '', $normalized);
    $normalized = ltrim($normalized, '/');
    if (stripos($normalized, 'storage/') === 0) return '../../' . $normalized;
    if (stripos($normalized, 'uploads/') === 0) return '../../storage/' . ltrim($normalized, '/');
    return '../../' . $normalized;
}

header('Content-Type: application/json');
$postId = isset($_GET['post_id']) ? (int)$_GET['post_id'] : 0;
if ($postId <= 0 || !isset($conn) || !$conn instanceof mysqli) {
    echo json_encode(['photos'=>[], 'videos'=>[]]);
    exit;
}

$photos = [];
$stmt = $conn->prepare('SELECT photo_path FROM post_photos WHERE post_id = ? ORDER BY id ASC');
$stmt->bind_param('i', $postId);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $photos[] = resolve_media_path($row['photo_path']);
}
$stmt->close();

$videos = [];
$stmt = $conn->prepare('SELECT video_path FROM post_videos WHERE post_id = ? ORDER BY id ASC');
$stmt->bind_param('i', $postId);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $videos[] = resolve_media_path($row['video_path']);
}
$stmt->close();

echo json_encode(['photos'=>$photos, 'videos'=>$videos]);
