# Price Comparison Backend Service

A backend service built with PHP 8.3 and Symfony 7 that fetches competitor pricing data from multiple sources, finds the lowest prices per product, and exposes a secure REST API to retrieve pricing information.

## Architecture

This project follows Domain-Driven Design (DDD) and SOLID principles with a clean architecture:

- **Domain Layer**: Contains entities, value objects, and interfaces
- **Application Layer**: Contains use cases and application services
- **Infrastructure Layer**: Contains implementations of domain interfaces
- **UI Layer**: Contains controllers and console commands

## Features

- Fetches pricing data from multiple external APIs (simulated)
- Aggregates and stores the lowest price per product
- Secure REST API with token-based authentication
- Console command for price aggregation
- Docker containerization with MySQL and Redis
- Clean architecture following DDD principles

## Requirements

- Docker and Docker Compose
- PHP 8.3+ (if running locally)
- Composer (if running locally)

## Setup Instructions

### 1. Clone and Setup

```bash
git clone <repo-url>
cd price-comparison-api
```

### 2. Build and Start Docker Containers

```bash
docker-compose up -d --build
```

This will start:
- PHP 8.3-FPM container
- Nginx web server (port 8085)
- MySQL 8.0 database (port 3308)
- Redis cache (port 6385)

### 3. Install Dependencies

```bash
docker-compose exec php composer install
```

### 4. Run Database Migrations

```bash
docker-compose exec php bin/console doctrine:migrations:migrate --no-interaction
```

### 5. Aggregate Sample Price Data

```bash
# Aggregate prices for all sample products (123, 456, 789)
docker-compose exec php bin/console app:aggregate-prices

# Or aggregate for a specific product
docker-compose exec php bin/console app:aggregate-prices --single=123
```

## API Usage

The API is available at `http://localhost:8085/api` and requires authentication via the `X-API-Key` header.

### Authentication

Include the API key in your requests:
```
X-API-Key: your-secret-api-key
```

### Endpoints

#### Get All Lowest Prices
```bash
curl -H "X-API-Key: your-secret-api-key" \
     http://localhost:8085/api/prices
```

Response:
```json
[
  {
    "product_id": "123",
    "vendor": "VendorThree",
    "price": 16.75,
    "fetched_at": "2025-06-29T14:00:00Z"
  },
  {
    "product_id": "456",
    "vendor": "VendorFour",
    "price": 31.50,
    "fetched_at": "2025-06-29T14:05:00Z"
  }
]
```

#### Get Price for Specific Product
```bash
curl -H "X-API-Key: your-secret-api-key" \
     http://localhost:8085/api/prices/123
```

Response:
```json
{
  "product_id": "123",
  "vendor": "VendorThree",
  "price": 16.75,
  "fetched_at": "2025-06-29T14:00:00Z"
}
```

## Code Structure

```
src/
├── Application/
│   └── Service/           # Application services (use cases)
├── Domain/
│   ├── Entity/           # Domain entities
│   ├── Repository/       # Repository interfaces
│   ├── Service/          # Domain service interfaces
│   └── ValueObject/      # Value objects
├── Infrastructure/
│   ├── Entity/           # Doctrine entities
│   ├── Repository/       # Repository implementations
│   └── Service/          # External service implementations
└── UI/
    ├── Command/          # Console commands
    ├── Controller/       # API controllers
    └── Security/         # Authentication services
```

## Mock Data Sources

The service simulates two external APIs with different data structures:

**API One** (ShopA, ShopB, ShopC, ShopD, ShopE):
```json
{
  "product_id": "123",
  "prices": [
    { "vendor": "ShopA", "price": 19.99 },
    { "vendor": "ShopB", "price": 17.49 }
  ]
}
```

**API Two** (VendorOne, VendorTwo, VendorThree, VendorFour, VendorFive):
```json
{
  "id": "123",
  "competitor_data": [
    { "name": "VendorOne", "amount": 20.49 },
    { "name": "VendorTwo", "amount": 18.99 }
  ]
}
```

## Commands

```bash
# Aggregate prices for all products
docker-compose exec php bin/console app:aggregate-prices

# Aggregate prices for specific products
docker-compose exec php bin/console app:aggregate-prices 123,456

# Aggregate prices for a single product
docker-compose exec php bin/console app:aggregate-prices --single=789

# Clear cache
docker-compose exec php bin/console cache:clear

# View logs
docker-compose logs php
```

## Development

### Running Tests
```bash
docker-compose exec php vendor/bin/phpunit tests
```

### Code Quality
```bash
# PHP CS Fixer
docker-compose exec php vendor/bin/php-cs-fixer fix --verbose

# PHPStan
docker-compose exec php vendor/bin/phpstan analyze
```

### Database Management

Access PHPMyAdmin at `http://localhost:4698`:
- Username: `app`
- Password: `app`

## Environment Variables

Key environment variables in `.env`:

- `DATABASE_URL`: MySQL connection string
- `API_KEY`: Secret key for API authentication
- `REDIS_URL`: Redis connection string
- `APP_ENV`: Application environment (dev/prod)

## Business Logic

1. **Price Fetching**: Multiple price fetchers implement the `PriceFetcherInterface`
2. **Aggregation**: The `PriceAggregationService` collects prices from all sources
3. **Storage**: Only the lowest price per product is stored in the database
4. **API**: Secure endpoints expose the aggregated pricing data

## Future Enhancements

- Scheduled price updates via cron jobs
- Retry logic for failed API calls
- Enhanced caching with Redis
- Rate limiting for API endpoints
- Token authentification system
- Health check endpoints
