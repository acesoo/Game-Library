# 🎮 GameDB — Video Game Library

A full-stack PHP CRUD web application for managing a video game collection, inspired by Steam's UI aesthetic.

**Live Demo:** [game-library-production.up.railway.app](https://game-library-production.up.railway.app)

---

## 📸 Preview

> A dark, Steam-inspired game library where you can browse, add, edit, and delete video game records complete with cover art, pricing, ratings, and more.

---

## 🛠️ Tech Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8.2 |
| Database | MySQL 8 (Railway) |
| Frontend | Bootstrap 5 + Vanilla JS |
| Fonts | Rajdhani + Barlow (Google Fonts) |
| Hosting | Railway |
| Local Dev | Laragon + HeidiSQL |
| Version Control | Git + GitHub |

---

## 📁 Project Structure

```
gamedb/
├── index.php       # Game listing page (Read + Delete)
├── create.php      # Add new game form (Create)
├── edit.php        # Edit existing game form (Update)
├── db.php          # PDO MySQL database connection
├── upload.php      # Local image upload handler
├── schema.sql      # MySQL table schema
├── Dockerfile      # Railway deployment config
├── uploads/        # Uploaded cover images
└── README.md
```

---

## 🗃️ Database Schema

Table: `games`

| Column | Type | Description |
|---|---|---|
| `id` | INT AUTO_INCREMENT | Primary key |
| `title` | VARCHAR(200) | Game title |
| `genre` | VARCHAR(80) | Genre (RPG, FPS, etc.) |
| `developer` | VARCHAR(150) | Developer studio |
| `price` | DECIMAL(8,2) | Price in USD (0 = Free) |
| `release_date` | DATE | Official release date |
| `platform` | VARCHAR(100) | Platform(s) available |
| `rating` | TINYINT | Rating out of 10 |
| `description` | TEXT | Short game description |
| `cover_path` | VARCHAR(255) | Path to uploaded cover image |
| `created_at` | TIMESTAMP | Auto-set on insert |
| `updated_at` | TIMESTAMP | Auto-updated on change |

---

## ✨ Features

- **Full CRUD** — Create, Read, Update, Delete game records
- **Image Upload** — Upload cover art stored in `/uploads/`
- **Search & Filter** — Search by title/developer, filter by genre
- **Rating Slider** — Interactive 0–10 rating input
- **Stats Bar** — Live count of games, average price, average rating
- **Responsive** — Works on desktop and mobile via Bootstrap 5
- **Validation** — All required fields validated using `empty()` with inline error messages
- **PDO Prepared Statements** — Protection against SQL injection

---

## 🚀 Local Development (Laragon)

### Prerequisites
- [Laragon](https://laragon.org) installed
- PHP 8.2+
- MySQL via HeidiSQL

### Setup

**1. Clone the repo**
```bash
git clone https://github.com/YOUR_USERNAME/Game-Library.git
cd Game-Library
```

**2. Create the database**
- Open HeidiSQL in Laragon
- Create a new database called `gamedb`
- Run `schema.sql` via Query → New Query → F9

**3. Configure db.php**

The `db.php` auto-detects the environment. For local development it uses these defaults:
```
host:     localhost
database: gamedb
user:     root
password: (empty)
port:     3306
```

**4. Move to Laragon's www folder**
```
C:\laragon\www\Game-Library\
```

**5. Visit in browser**
```
http://localhost/Game-Library/
```

---

## ☁️ Deployment (Railway)

### Environment Variables

Set these in Railway → Game-Library service → Variables:

| Variable | Value |
|---|---|
| `MYSQL_HOST` | `mysql.railway.internal` |
| `MYSQL_DATABASE` | `railway` |
| `MYSQL_USER` | `root` |
| `MYSQL_PASSWORD` | *(from Railway MySQL service)* |
| `MYSQL_PORT` | `3306` |

### Deploy Steps

1. Push code to GitHub
2. Connect GitHub repo to Railway
3. Add MySQL service in Railway
4. Run `schema.sql` via HeidiSQL connected to Railway's public URL
5. Set environment variables above
6. Generate domain in Settings → Networking
7. Done! 🎉

### Database Management (from anywhere)

Use **HeidiSQL** with Railway's public credentials:
- **Host:** `crossover.proxy.rlwy.net`
- **Port:** `50724`
- **User:** `root`
- **Library:** `libmariadb.dll`

---

## 📝 CRUD Operations

| Operation | File | Method |
|---|---|---|
| **Read** | `index.php` | `GET` — lists all games |
| **Create** | `create.php` | `POST` — inserts new game |
| **Update** | `edit.php` | `POST` — updates existing game |
| **Delete** | `index.php` | `POST` — deletes by ID |

---

## 🔒 Security

- All queries use **PDO prepared statements** (no raw SQL with user input)
- All output uses `htmlspecialchars()` to prevent XSS
- File uploads are validated by **MIME type** and capped at **5MB**
- Only JPEG, PNG, GIF, and WEBP images are accepted

---

## 👨‍💻 Author

Made with ❤️ as a PHP CRUD activity using PDO.
