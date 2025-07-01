# Recruitment Application

<p align="center">
  <img src="public/images/logo.png" alt="Recruitment App Logo" width="200">
</p>

## Overview

Recruitment Application is a feature-rich platform built with Laravel 12 and Orchid to manage the entire hiring workflow:

- **Job Management**: CRUD operations for job listings including title, description, location, type, department, status, posted and expiry dates.
- **Applicant Management**: Manage applicant profiles: personal details, resume, cover letter, LinkedIn URL.
- **Application Tracking**: Link applicants to jobs with status workflows: submitted, reviewed, rejected, accepted.
- **Custom Questions**: Define custom questions per job and capture applicant responses.
- **UTM Tracking**: Automatically capture and store UTM parameters (`utm_source`, `utm_medium`, etc.) for each application.
- **Admin Panel**: Built with Orchid; user-friendly screens and layouts.

## Features

- Job listings: Create, read, update, delete job posts
- Applicants: Manage applicant details and documents
- Applications: Track application statuses and history
- Questionnaires: Custom questions per job
- UTM & Analytics: Session-based UTM parameter capture

## Requirements

- PHP >= 8.1
- Laravel 12
- MySQL or PostgreSQL
- Composer
- Node.js & NPM
- Docker & Docker Compose (optional)

## Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/your-org/recruitment-app.git
   cd recruitment-app
   ```

2. Install composer dependencies:
   ```bash
   composer install
   ```

3. Copy `.env.example` to `.env` and generate key:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. Set up database and mail in `.env`:
   ```dotenv
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=your_database
   DB_USERNAME=your_username
   DB_PASSWORD=your_password

   MAIL_MAILER=smtp
   MAIL_HOST=smtp.mailtrap.io
   MAIL_PORT=2525
   MAIL_USERNAME=null
   MAIL_PASSWORD=null
   MAIL_ENCRYPTION=null
   ```

5. Install frontend dependencies and build assets:
   ```bash
   npm install
   npm run dev
   ```

## Database Setup

```bash
php artisan migrate
php artisan db:seed
```

- Seeds include default Orchid admin and sample data.

## Orchid Admin Setup

```bash
php artisan orchid:install
php artisan orchid:admin admin@example.com --password=secret
```

Access the admin panel at `http://localhost:8000/admin`.

## Docker Setup (Optional)

1. Build and run containers:
   ```bash
   docker-compose up -d --build
   ```
2. Enter the app container:
   ```bash
   docker-compose exec app bash
   ```
3. Run migrations and seeders inside:
   ```bash
   php artisan migrate --seed
   ```

## Running the Application

```bash
php artisan serve
```
Visit `http://localhost:8000` for the public site.

## Running Tests

```bash
php artisan test
```

## Contributing

Contributions are welcome! Please:
- Fork the repository
- Create a feature branch
- Ensure code follows PSR-12 and Laravel conventions
- Write tests for any new functionality
- Submit a pull request

## Environment Variables

- `UTM_TTL` (minutes): Lifetime for stored UTM parameters (default: 1440)

## License

This project is licensed under the MIT License.
