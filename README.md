💻 Backend: Laravel API (ip-geo-api)
IP Geolocation API
This is the backend service for the IP Geolocation Tracker, responsible for authentication, data persistence, and history management.

🛠 Tech Stack
Framework: Laravel 11

Database: MariaDB / MySQL

Auth: Laravel Sanctum (Token-based)

⚙️ Installation
Clone and Enter: Navigate to the ip-geo-api directory.

Install Dependencies:

Bash
composer install
Environment Setup:

Bash
cp .env.example .env
php artisan key:generate
Database Configuration: Update the .env file with your local database credentials (DB_DATABASE, DB_USERNAME, DB_PASSWORD).

Migrate and Seed:

Bash
php artisan migrate --seed
Note: This creates a test user: admin@email.com / password123.

📡 API Endpoints
POST /api/login - Authenticates user and returns a Bearer token.

GET /api/history - Fetches all searches for the authenticated user.

POST /api/history - Saves a new search result to the database.

DELETE /api/history - Bulk deletes selected history entries.
