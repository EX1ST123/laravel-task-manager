# TaskFlow

A task management web application built with Laravel 11 (API backend) and React (frontend).

---

## Requirements

Make sure the following are installed before running the project:

- [PHP 8.2+](https://www.php.net/downloads)
- [Composer](https://getcomposer.org/)
- [MySQL](https://dev.mysql.com/downloads/mysql/)
- [Node.js](https://nodejs.org/) (LTS version recommended)

---

## Database Setup

1. Open phpMyAdmin or your MySQL client
2. Create a new database named `taskflow`
3. Import the provided `taskflow.sql` file into that database

---

## Backend Setup

1. Copy the environment file:
```bash
cp .env.example .env
```

2. Open `.env` and fill in your MySQL credentials:
```ini
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=taskflow
DB_USERNAME=root
DB_PASSWORD=your_password
```

3. Install PHP dependencies:
```bash
composer install
```

4. Generate the application key:
```bash
php artisan key:generate
```

5. Start the backend server:
```bash
php artisan serve
```

The API will be available at `http://localhost:8000`

---

## Frontend Setup

Open a second terminal and run:

```bash
cd frontend
npm install
npm run dev
```

The app will be available at `http://localhost:5173`

---

## AI Features (Optional)

The app includes an AI productivity assistant powered by Google Gemini. To enable it, add your Gemini API key to `.env`:

```ini
GEMINI_API_KEY=your_gemini_api_key
```

You can get a free API key at [https://aistudio.google.com](https://aistudio.google.com)

If no key is provided, the rest of the app works normally — only the AI chat and tips features will be unavailable.

---

## API Endpoints

| Method | URL | Auth |
|--------|-----|------|
| POST | /api/auth/register | public |
| POST | /api/auth/login | public |
| POST | /api/auth/logout | required |
| GET | /api/tasks | required |
| POST | /api/tasks | required |
| GET | /api/tasks/stats | required |
| GET | /api/tasks/{id} | required |
| PUT | /api/tasks/{id} | required |
| DELETE | /api/tasks/{id} | required |
| PATCH | /api/tasks/{id}/complete | required |
| PATCH | /api/tasks/{id}/in-progress | required |
| GET | /api/users/me | required |
| PUT | /api/users/me | required |
| POST | /api/ai/chat | required |
| POST | /api/ai/tip | required |
