# Video Embed Platform v1.1

MVP video embed platform with:
- Admin-only dashboard; no public homepage/catalog
- Video, category, token and allowed-domain management
- JavaScript advertising campaigns
- Adsterra / Clickadu weighted rotation
- Session cooldown and rotation event logging
- Server-side publisher API statistics with caching
- Cloudflare R2 configuration
- MySQL schema

## Security
Never commit .env, API keys, R2 secrets, or publisher credentials.

## Local installation
1. Install PHP 8.2+, MySQL/MariaDB and Composer.
2. Copy .env.example to .env and configure the database.
3. Run composer install --no-dev.
4. Import database.sql.
5. Configure the web root and open /admin/login.php.
6. Configure R2 and publisher API credentials only in the server environment.

## Advertising
Adsterra and Clickadu campaign code is executed as JavaScript, not as a plain navigation link. The embed player requests the server-side ad endpoint; API credentials are never exposed to the browser.

## Earnings
The Ads dashboard reads publisher-reported revenue from the network APIs. Local ad events are analytics only and are not treated as earnings.