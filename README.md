# Video Embed Platform v1.3

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
- Responsive admin dashboard

## Stream delivery
Cloudflare R2 is not part of the application UI or delivery path. Each video can have one or more external stream URLs (HLS `.m3u8`, MP4, or an external embed endpoint). The embed player uses the first active stream link and counts its requests.

## Migration / update
For an existing installation:
1. `git pull origin main`
2. Run `install/migrate_v1_3.sql` against the existing database.
3. Make sure PHP can access the project files and the web root is correct.
4. Hard-refresh the browser after deployment.

Example:
`mysql -u DATABASE_USER -p DATABASE_NAME < install/migrate_v1_3.sql`

## Advertising
Adsterra and Clickadu snippets are stored as JavaScript campaign code and selected by server-side rotation rules. Publisher API credentials stay server-side. Earnings shown in the dashboard are publisher-reported API revenue, not local estimates.

## Security
Never commit `.env`, API keys, publisher credentials or other secrets.
