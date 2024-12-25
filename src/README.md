# Innoscripta News App

This project is a Laravel-based application for managing user authentication, preferences, and a dynamic news feed. The application is designed to fetch, manage, and serve customized news content to users based on their preferences. It uses Docker for containerized deployment and includes a MySQL database, Redis cache, PHP backend, and Nginx server.

---

## Table of Contents

1. [Features](#features)
2. [Technologies Used](#technologies-used)
3. [Installation](#installation)
4. [Usage](#usage)
5. [Docker Setup](#docker-setup)
6. [Project Structure](#project-structure)
7. [API Endpoints](#api-endpoints)
8. [Database Migrations](#database-migrations)
9. [Seeding Data](#seeding-data)
10. [License](#license)

---

## Features

- User registration and login using Laravel Sanctum.
- Token-based authentication.
- Customizable user preferences for news sources, categories, and authors.
- Dynamic news feed retrieval based on user preferences.
- Full-text search for articles.
- Background job scheduling and database migrations.

---

## Technologies Used

- **Backend:** Laravel 11
- **Database:** MySQL 8.0
- **Cache:** Redis
- **Containerization:** Docker, Docker Compose
- **Web Server:** Nginx
- **Language:** PHP 8.2

---

## Installation

1. Clone the repository:
    ```bash
    git clone https://github.com/Tohid-Hemmati/innoscripta.git
    cd innoscripta-news-app
    ```

2. Set up environment variables:
    ```bash
    cp .env.example .env
    ```

3. Configure `.env` file for database, Redis, and Laravel settings.

4. Install dependencies:
    ```bash
    composer install
    ```
---

## Docker Setup

1. Build and start Docker containers:
    ```bash
    docker-compose up --build
    ```

2. Verify containers are running:
    ```bash
    docker ps
    ```

3. Access the app at [http://localhost:8000](http://localhost:8000).

---

## Project Structure

- `docker/`: Docker configurations for PHP, Nginx, and MySQL.
- `src/`: Laravel application source code.
- `docker/php/Dockerfile`: PHP application container definition.
- `docker/nginx/default.conf`: Nginx configuration.
- `database/migrations/`: Database migrations.
- `database/seeders/`: Data seeders for testing and default data.

---

## API Endpoints

### Authentication

- **Register:** `POST /api/register`
- **Login:** `POST /api/login`
- **Logout:** `POST /api/logout`
- **Forgot Password:** `POST /api/password/forgot`
- **Reset Password:** `POST /api/password/reset`

### Articles

- **Fetch All Articles:** `GET /api/articles`
- **Fetch Article by ID:** `GET /api/articles/{id}`
- **Fetch Preferred News:** `GET /api/articles/preferred`
- **Set Preferences:** `POST /api/articles/preferences`
