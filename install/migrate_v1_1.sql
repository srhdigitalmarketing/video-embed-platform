ALTER TABLE ad_campaigns
  ADD COLUMN ad_type ENUM('javascript','html','external') NOT NULL DEFAULT 'javascript' AFTER network,
  ADD COLUMN weight INT UNSIGNED NOT NULL DEFAULT 100 AFTER priority,
  ADD COLUMN trigger_event ENUM('player_load','player_click','video_start','video_end','manual') NOT NULL DEFAULT 'player_click' AFTER frequency_minutes;
CREATE TABLE IF NOT EXISTS ad_rotation_events (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  campaign_id BIGINT UNSIGNED NOT NULL,
  video_id BIGINT UNSIGNED NULL,
  domain VARCHAR(255) NULL,
  device VARCHAR(30) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_campaign_date(campaign_id,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS api_cache (
  cache_key VARCHAR(190) PRIMARY KEY,
  payload LONGTEXT NOT NULL,
  expires_at DATETIME NOT NULL,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;