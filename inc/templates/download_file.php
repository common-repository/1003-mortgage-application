<?php

require_once(ABSPATH . 'wp-load.php');

// download_file.php
$post_id = $_POST['post_id'];

write_log('download_file.php: $post_id = ' . $post_id);

$post = get_post($post_id);
write_log($post);

// Get the file path from the source_result array using the $post_id
// $file_path = getSourceResultFilePath($post_id);

// // Set the appropriate headers for file download
// header('Content-Type: application/octet-stream');
// header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
// header('Content-Length: ' . filesize($file_path));

// // Output the file contents
// readfile($file_path);