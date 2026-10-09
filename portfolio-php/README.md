# PortfolioGen (PHP)

Plain PHP 8 + PDO + MySQL (Aiven). Deployed on Render with Docker.

## Run locally (VS Code terminal)
1. Install PHP 8.1+ (with pdo_mysql enabled)
2. Copy `.env.example` to `.env` and fill in your Aiven details
3. `php init-db.php`      (creates tables once)
4. `php -S localhost:3000 -t public public/index.php`
5. Open http://localhost:3000

## Folders
- `public/`  → web root: index.php (router), css/, js/, images/
- `src/`     → bootstrap.php (db + helpers) and views/
