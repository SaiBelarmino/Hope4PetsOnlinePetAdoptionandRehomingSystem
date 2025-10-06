-- Migration: create post_videos table
-- Run this against the hope4pets database

CREATE TABLE IF NOT EXISTS post_videos (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  post_id BIGINT UNSIGNED NOT NULL,
  video_path VARCHAR(500) NOT NULL,
  CONSTRAINT fk_postvideos_post FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
);
