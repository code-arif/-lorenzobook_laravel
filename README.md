# Lorenzobook Backend API

A robust, real-time backend application built with **Laravel 11**, designed to serve as the core infrastructure for the Lorenzobook platform. It features a comprehensive real-time chat system, role-based access control, secure API authentication, and seamless third-party integrations for payments and communications.

## 🚀 Key Features

- **Real-Time Communication:** Full-featured chat system supporting single conversations, group chats, and broadcast channels. Includes real-time read receipts, typing indicators, multiple media file uploads (images, videos, voice notes), message muting, and chat history management using **Laravel Reverb** & **Pusher**.
- **Role-Based Access Control (RBAC):** Integrated using `spatie/laravel-permission` to carefully handle various user roles including Admin, Developer, Client, Retailer, Trainer, and User.
- **Secure Authentication:** RESTful API authentication via **Laravel Sanctum** and **JWT**, along with OAuth support using **Socialite** and **Firebase**.
- **Payment Processing:** Integrated **Stripe** for secure financial transactions and subscriptions.
- **SMS & Communications:** Integrated **Twilio SDK** for SMS and OTP notifications, alongside robust email handling.
- **Advanced Data Handling:** Server-side data processing using **Yajra DataTables**, plus automated PDF document generation via **DOMPDF**.

## 🛠️ Tech Stack

- **Framework:** Laravel 11 (PHP 8.2+)
- **Frontend / Scaffolding:** TailwindCSS, Alpine.js, Vite
- **WebSockets:** Laravel Reverb & Laravel Echo
- **Database:** MySQL / SQLite
- **Security:** Google reCAPTCHA, Sanctum, JWT

## ⚙️ Prerequisites

Before you begin, ensure you have the following installed on your machine:
- PHP >= 8.2
- Composer
- Node.js & NPM
- MySQL or SQLite

## 📦 Installation & Setup

1. **Install PHP dependencies:**
   ```bash
   composer update
   ```

2. **Install Node.js dependencies and build assets:**
   ```bash
   npm i
   npm run build
   ```

3. **Database Setup:**
   Configure your `.env` file with your database credentials (you can copy from `.env.example`), then run the migrations and seeders:
   ```bash
   php artisan migrate:fresh --seed
   ```

4. **Clear caches and optimize:**
   ```bash
   php artisan optimize:clear
   ```

## 🚀 Running the Application

To get the application fully running with real-time features, you need to start the following services (preferably in separate terminal tabs):

```bash
# 1. Start the Vite development server for frontend assets
npm run dev

# 2. Start the Laravel PHP development server
php artisan serve --host=0.0.0.0 --port=8050

# 3. Start the Laravel Reverb WebSocket server (for real-time chat)
php artisan reverb:start --debug

# 4. Start the Queue Worker (for background jobs, broadcasting, and emails)
php artisan queue:work
```

## 🔐 Demo / Testing Accounts

You can use the following seeded credentials to test the application across different user roles. 

### Web Portal Accounts (Guard: `web`)
| Role | Email | Password |
|------|-------|----------|
| **Admin** | admin@admin.com | 12345678 |
| **Developer** | developer@developer.com | 12345678 |
| **Client** | client@client.com | 12345678 |
| **Retailer** | retailer@retailer.com | 12345678 |

### API / Mobile App Accounts (Guard: `api`)
| Role | Email | Password |
|------|-------|----------|
| **Trainer** | trainer@trainer.com | 12345678 |
| **User** | user@user.com | 12345678 |

---

*Developed and maintained for the Lorenzobook ecosystem.*
