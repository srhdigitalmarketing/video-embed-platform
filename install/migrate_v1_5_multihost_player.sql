-- Video Embed Platform v1.5
-- Adds player skin settings and external-host health monitoring.

CREATE TABLE IF NOT EXISTS stream_health_events (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  stream_id BIGINT UNSIGNED NOT NULL,
  video_id BIGINT UNSIGNED NOT NULL,
  event_type ENUM('timeout','load_error','manual_switch','play_failed') NOT NULL,
  client_domain VARCHAR(255) NULL,
  user_agent VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_health_date(stream_id, created_at),
  INDEX idx_health_video(video_id, created_at),
  CONSTRAINT fk_health_stream FOREIGN KEY(stream_id) REFERENCES stream_links(id) ON DELETE CASCADE,
  CONSTRAINT fk_health_video FOREIGN KEY(video_id) REFERENCES videos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO settings(setting_key,setting_value)
VALUES ('PLAYER_PLAY_COLOR','#2F80ED')
ON DUPLICATE KEY UPDATE setting_value=setting_value;

INSERT INTO settings(setting_key,setting_value)
VALUES ('PLAYER_FAILOVER_TIMEOUT','8000')
ON DUPLICATE KEY UPDATE setting_value=setting_value;
