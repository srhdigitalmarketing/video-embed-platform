# Video Embed Platform v1.5

Admin-only multi-host video embed platform with a lightweight player wrapper.

## Features
- Admin-only dashboard; no public homepage/catalog
- Video and category management
- Multiple external player/stream hosts per video
- Clean public player URL: `/play/ttXXXXXXXXX`
- Multi-host selector with active host state and loading indicator
- External iframe player wrapper; no requirement for the source to be `.m3u8`
- Allowed-domain protection
- Stream request counters
- JavaScript advertising campaigns
- Adsterra / Clickadu weighted rotation
- Advertisement edit/delete controls
- Server-side publisher API statistics with caching
- Responsive admin dashboard
- No Cloudflare R2 dependency

## External player architecture
A stream link is an external URL such as:
`https://ustreamplay.online/#pniy53`

The platform does not parse or transcode that page. It embeds the permitted external player inside the platform wrapper. Because the source is cross-origin, the platform does not attempt to modify the source page's internal controls or DOM.

## Public player
Example:
`https://gpt.ttobrut.site/play/tt408734383`

The public play key is separate from the internal token.

## Existing installation update
1. `git pull origin main`
2. Run `install/migrate_v1_4_play_url.sql` if play_key has not already been added.
3. Ensure `/play/...` is routed to `index.php` (see `deploy/nginx-play.conf`).
4. Hard-refresh the browser.

## Important browser limitation
The source host must allow iframe embedding. If the external host sends `X-Frame-Options` or a CSP `frame-ancestors` policy that blocks framing, the browser will refuse to display it.

## Advertising
Adsterra and Clickadu snippets are stored as JavaScript campaign code and selected by server-side rotation rules. Publisher API credentials stay server-side. Earnings shown in the dashboard are publisher-reported API revenue, not local estimates.

## Security
Never commit `.env`, API keys, publisher credentials or other secrets.
