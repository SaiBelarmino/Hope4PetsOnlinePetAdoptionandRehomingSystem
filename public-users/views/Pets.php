<?php
$pageTitle = 'Success Stories';
include __DIR__ . '/../include/header.php';
include __DIR__ . '/../include/topbar.php';

require_once __DIR__ . '/../controllers/CommunityController.php';
require_once __DIR__ . '/../../config/SessionManager.php';

$session = new SessionManager();
$userId = $session->get('user_id');
$stories = CommunityController::getStories();
?>
<link rel="stylesheet" href="assets/css/community.css" />
