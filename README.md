# TaskFlow — Laravel 11 + Sanctum

Complete API backend matching the original Spring Boot project.

---

## Setup (run these commands in order)

### 1. Install dependencies
```bash
composer install
```

### 2. Create your `.env` file
```bash
cp .env.example .env
```

Then open `.env` and fill in your PostgreSQL credentials:
```ini
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=taskflow        # must exist in PostgreSQL already
DB_USERNAME=postgres
DB_PASSWORD=your_password
```

> Create the database first if it doesn't exist:
> ```sql
> CREATE DATABASE taskflow;
> ```

### 3. Generate app key
```bash
php artisan key:generate
```

### 4. Run migrations
```bash
php artisan migrate
```

### 5. Start the server
```bash
php artisan serve
```
API is now at `http://localhost:8000`

---

## Frontend

Your React frontend is **unchanged** — just update `vite.config.js`:

```js
proxy: {
  '/api': {
    target: 'http://localhost:8000',  // was 8080
    changeOrigin: true,
  },
},
```

Then:
```bash
cd frontend
npm install
npm run dev
```

---

## API Endpoints

| Method | URL | Auth |
|--------|-----|------|
| POST | /api/auth/register | public |
| POST | /api/auth/login | public |
| POST | /api/auth/logout | ✓ |
| GET | /api/tasks | ✓ |
| POST | /api/tasks | ✓ |
| GET | /api/tasks/stats | ✓ |
| GET | /api/tasks/{id} | ✓ |
| PUT | /api/tasks/{id} | ✓ |
| DELETE | /api/tasks/{id} | ✓ |
| PATCH | /api/tasks/{id}/complete | ✓ |
| PATCH | /api/tasks/{id}/in-progress | ✓ |
| GET | /api/users/me | ✓ |
| PUT | /api/users/me | ✓ |
