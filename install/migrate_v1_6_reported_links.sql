-- Video Embed Platform v1.6
-- Automatic reported-link queue for broken external player hosts.
-- Run once against an existing v1.5 database.

CREATE TABLE IF NOT EXISTS reported_links (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  stream_id BIGINT UNSIGNED NOT NULL,
  video_id BIGINT UNSIGNED NOT NULL,
  link_url TEXT NOT NULL,
  reason VARCHAR(100) NOT NULL DEFAULT 'not working',
  reports INT UNSIGNED NOT NULL DEFAULT 1,
  status ENUM('pending','fixed','ignored') NOT NULL DEFAULT 'pending',
  notes TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_report_status(status,updated_at),
  INDEX idx_report_stream(stream_id),
  INDEX idx_report_video(video_id),
  CONSTRAINT fk_report_stream FOREIGN KEY(stream_id) REFERENCES stream_links(id) ON DELETE CASCADE,
  CONSTRAINT fk_report_video FOREIGN KEY(video_id) REFERENCES videos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
