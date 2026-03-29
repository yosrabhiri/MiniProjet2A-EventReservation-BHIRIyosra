# 🗓️ Event Management Web Application

A web application built with **Symfony** for managing events and reservations.

---

## ✨ Features

### 👤 User Features
- User authentication (JWT login)
- Browse events retrieved from the database
- Filter events by various criteria
- View detailed event information:
  - Description, Date, Location, Image
- Reservation form (Name, Email, Phone number)
- Reservations stored in the database
- Confirmation message after booking

### 🔧 Admin Features
- Admin authentication (username/password)
- Dashboard displaying all events
- Full CRUD operations on events (Create, Read, Update, Delete)
- View reservations per event *(click an event row in the dashboard)*

---

## 🚀 Installation & Setup

### Prerequisites
- [Docker](https://www.docker.com/)
- [Docker Compose](https://docs.docker.com/compose/)

### 1. Start the Application
```bash
docker compose up -d
```

### 2. Run Database Migrations
```bash
docker compose exec php php bin/console doctrine:migrations:migrate
```

### 3. Load Fixtures
```bash
docker compose exec php php bin/console doctrine:fixtures:load
```

---

## 🌐 Access the Application

| Role  | URL                              | Credentials (fixtures)       |
|-------|----------------------------------|------------------------------|
| Admin | http://localhost:8000/admin/login | `admin` / `admin123`         |
| User  | http://localhost:8000/login       | Register via the signup page |

---

## 📧 Email Configuration

The application uses **Gmail SMTP**. To configure it:

1. Go to [Google Account Security](https://myaccount.google.com/security)
2. Enable **2-Step Verification**
3. Generate an **App Password**
4. Update your `.env` file:
```env
MAILER_DSN=smtp://YOUR_EMAIL:APP_PASSWORD@smtp.gmail.com:587?encryption=tls&auth_mode=login
```

**Example:**
```env
MAILER_DSN=smtp://example@gmail.com:abcd1234abcd1234@smtp.gmail.com:587?encryption=tls&auth_mode=login
```

---

## 🔐 Security & Authentication

This project uses a **stateless JWT authentication** system:

- Users authenticate with their login credentials
- A **JWT token** is issued to access secured routes
- A **refresh token** mechanism handles token expiration
