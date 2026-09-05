# Video Embed Platform v1.2

Admin-only video embed platform with external Stream Links and advertising management.

## Features
- Admin-only dashboard; no public homepage/catalog
- Video, category, embed token and allowed-domain management
- Multiple external stream links per video
- Stream link request counters and active/inactive status
- JavaScript advertising campaigns
- Adsterra / Clickadu weighted rotation
- Advertisement edit/delete controls
- Server-side publisher API statistics with caching
- MySQL schema and installer

## Stream delivery
Cloudflare R2 is no longer part of the application UI or delivery path. Each video can have one or more external stream URLs (HLS `.m3u8` or MP4). The embed player uses the first active stream link and counts its requests.

## Security
Never commit `.env`, API keys, publisher credentials or other secrets.

## Local installation
1. Install PHP 8.2+, MySQL/MariaDB.
2. Copy `.env.example` to `.env` and configure the database.
3. `composer install --no-dev`
4. Import `database.sql` or run the installer.
5. Open `/admin/login.php`.
6. For an existing v1.1 installation, run `install/migrate_v1_2.sql`.

## Advertising
Adsterra and Clickadu snippets are stored as JavaScript campaign code and selected by server-side rotation rules. Publisher API credentials stay server-side. Earnings shown in the dashboard are publisher-reported API revenue, not local estimates.