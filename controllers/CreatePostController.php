<?php
// Assuming a function `insertPost` exists to handle post insertion
// and a function `uploadMedia` exists to handle media uploads

// Get the content and media from the request
$content = trim($_POST['content'] ?? '');
$content = empty($content) ? null : $content;
$media = $_FILES['media'] ?? [];

// Insert the post with content (null if empty)
$postId = insertPost($content);

// Handle media uploads if present
if (!empty($media['name'][0])) {
    // Loop through each file and upload
    foreach ($media['name'] as $key => $filename) {
        // Call the function to upload media file
        // Assuming `uploadMedia` returns the media ID or path
        $mediaId = uploadMedia($media['tmp_name'][$key], $filename);

        // ... code to associate the uploaded media with the post using $postId ...
    }
}

// Redirect or respond with success
// ...existing code...