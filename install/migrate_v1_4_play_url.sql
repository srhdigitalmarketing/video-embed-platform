-- Add clean public playback key support, e.g. /play/tt408734383
ALTER TABLE embed_tokens ADD COLUMN play_key VARCHAR(24) NULL UNIQUE AFTER token;

-- Generate keys for existing embeds that do not have one.
UPDATE embed_tokens
SET play_key = CONCAT('tt', LPAD(id, 9, '0'))
WHERE play_key IS NULL OR play_key='';

CREATE INDEX idx_embed_play_key ON embed_tokens(play_key);
