-- v1.5: External Multi-Host Player
-- The stream URL is an external player/embed URL, not a direct .m3u8 source.
-- Existing stream_links rows remain valid; this migration only documents the new model.
-- No schema change is required because stream_links.url already stores external URLs.

-- Optional cleanup for an old database that still has obsolete R2 columns:
-- ALTER TABLE videos DROP COLUMN r2_key;
-- ALTER TABLE videos DROP COLUMN hls_master_key;
