# Banking System

Laravel + Inertia.js (Vue 3) banking demo with wallet operations (deposit / withdraw / transfer) and transaction history.

## Tech Stack

- **Backend**: Laravel 12, PHP 8.2+
- **Auth**: Laravel Fortify + Sanctum
- **Frontend**: Vue 3 + Inertia.js + Vite

## Requirements

- Docker + Docker Compose

- Node.js + npm

## Installation (Docker Compose)

This project ships with a `docker-compose.yml` that runs:

- `nginx` (exposed on `http://localhost:9000`)
- `app` (PHP-FPM container)
- `db` (MySQL 8, exposed on host port `3307`)
- `phpmyadmin` (exposed on `http://localhost:8080`)

### 1) Start containers

```bash
docker compose up -d --build
```

### 2) Create `.env`

```bash
cp .env.example .env
```

### 3) Install dependencies & generate key (inside the app container)

```bash
docker exec -it banking-app composer install
docker exec -it banking-app php artisan key:generate
```

### 4) Run migrations / seed

```bash
docker exec -it banking-app php artisan migrate
docker exec -it banking-app php artisan db:seed
```

### 5) Frontend assets

You can build assets locally:

```bash
npm run build
```

## Running

- App (Nginx): `http://localhost:9000`
- phpMyAdmin: `http://localhost:8080`

## Key Pages (Web)

- **/**: Welcome page (redirects authenticated users to Profile)
- **/profile**: Profile page
- **/transactions**: Transaction history
- **/wallet**: Deposit / Withdraw / Transfer UI

## Postman Collection

A Postman collection is included in this repository:

- `Postman/app banking.postman_collection.json`

Notes:

- Wallet endpoints require the `Idempotency-Key` header.
- Requests use Bearer authentication with `{{accessToken}}`.

## Scripts

### Composer

- `composer install`

