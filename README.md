# 💻 Backend: Laravel API — `ip-geo-api`

The backend service for the IP Geolocation Tracker, handling authentication, data persistence, and search history management.

---

## 🛠 Tech Stack

| Layer | Technology |
|---|---|
| Framework | Laravel 12 |
| Database | MariaDB / MySQL |
| Auth | Laravel Sanctum (Token-based) |

---

## ⚙️ Installation

### 1. Navigate to the directory
```bash
cd ip-geo-api
```

### 2. Install dependencies
```bash
composer install
```

### 3. Environment setup
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Configure your database

Open the `.env` file and update the following with your local credentials:

```env
DB_DATABASE=your_database_name
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 5. Migrate and seed
```bash
php artisan migrate --seed
```

> **Note:** The seeder creates a default test user: `admin@email.com` / `password123`

---

## 📡 API Endpoints

All endpoints are prefixed with `/api` and require a Bearer token unless stated otherwise.

| Method | Endpoint | Auth Required | Description |
|---|---|---|---|
| `POST` | `/api/login` | ❌ | Authenticates user and returns a Bearer token |
| `GET` | `/api/history` | ✅ | Fetches all searches for the authenticated user |
| `POST` | `/api/history` | ✅ | Saves a new search result to the database |
| `DELETE` | `/api/history` | ✅ | Bulk deletes selected history entries |

### Authentication

Include the token returned from `/api/login` in the `Authorization` header for all protected routes:

```
Authorization: Bearer <your_token_here>
```
