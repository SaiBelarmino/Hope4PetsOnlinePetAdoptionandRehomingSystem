<?php
// Application level config helpers
// Determine base path relative to document root for consistent asset URLs
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$dir = rtrim(str_replace('index.php','',dirname($scriptName)), '/\\');
if (!defined('APP_BASE_PATH')) {
    define('APP_BASE_PATH', $dir === '' ? '' : $dir);
}
?>
