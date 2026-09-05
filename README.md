# Video Embed Platform v1.4

Admin-only video embed platform with clean public player URLs.

## Features
- Admin-only dashboard; no public homepage/catalog
- Video, category, embed token and allowed-domain management
- Multiple external stream links per video
- Stream request counters and active/inactive status
- Clean player URL format: `/play/ttXXXXXXXXX`
- JavaScript advertising campaigns
- Adsterra / Clickadu weighted rotation
- Advertisement edit/delete controls
- Server-side publisher API statistics with caching
- Responsive admin dashboard
- No Cloudflare R2 dependency

## Public player
Example:
`https://gpt.ttobrut.site/play/tt408734383`

The public key is separate from the internal 64-character token. Existing tokens receive a generated key during migration.

## Existing installation update
1. `git pull origin main`
2. Run `install/migrate_v1_4_play_url.sql` against the existing database.
3. Ensure the web server sends `/play/...` requests to the project's `index.php` front controller. An Nginx example is provided in `deploy/nginx-play.conf`.
4. Hard-refresh the browser.

Example:
`mysql -u DATABASE_USER -p DATABASE_NAME < install/migrate_v1_4_play_url.sql`

## Nginx / aaPanel
The project contains a location rule for:
`location ^~ /play/ { try_files $uri $uri/ /index.php?$query_string; }`

If aaPanel already uses a PHP front-controller `try_files` rule for the site, the bundled `index.php` can route `/play/{key}` itself. Otherwise add the provided location block in the site's Nginx configuration.

## Stream delivery
Cloudflare R2 is not part of the application UI or delivery path. Each video has one or more external stream URLs (HLS `.m3u8`, MP4, or another permitted player endpoint). The embed player uses the first active stream link and counts requests.

## Advertising
Adsterra and Clickadu snippets are stored as JavaScript campaign code and selected by server-side rotation rules. Publisher API credentials stay server-side. Earnings shown in the dashboard are publisher-reported API revenue, not local estimates.

## Security
Never commit `.env`, API keys, publisher credentials or other secrets.
