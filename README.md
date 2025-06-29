# Price Comparison Backend Service

A backend service built with PHP 8.3 and Symfony 7 that fetches competitor pricing data from multiple sources, finds the lowest prices per product, and exposes a secure REST API to retrieve pricing information.

## Architecture

This project follows Domain-Driven Design (DDD) and SOLID principles with a clean architecture:

- **Domain Layer**: Contains entities, value objects, and interfaces
- **Application Layer**: Contains use cases, DTOs, and application services
- **Infrastructure Layer**: Contains implementations of domain interfaces, caching, and external services
- **UI Layer**: Contains controllers, console commands, and security

## Features

- **Multi-source Price Fetching**: Dynamic API sources stored in database with configurable formats
- **Intelligent Caching**: Redis-based caching with configurable TTL
- **Retry Logic**: Exponential backoff for failed API calls
- **Price Aggregation**: Finds and stores the lowest price per product
- **Secure REST API**: Token-based authentication with X-API-Key header
- **Data Fixtures**: Clean separation of test data from business logic
- **Enhanced Monitoring**: Detailed aggregation results and error tracking
- **Console Commands**: Rich CLI interface with progress bars and detailed reporting

## Technical Improvements

### ✅ **Caching Layer** (Redis)
- Product price caching (10 minutes TTL)
- API response caching (5 minutes TTL)
- All prices cache (5 minutes TTL)
- Cache invalidation on updates

### ✅ **Retry Logic**
- Exponential backoff strategy
- Configurable retry attempts (default: 3)
- Specific exception handling
- Detailed error logging

### ✅ **Data Fixtures**
- Clean separation from business logic
- Multiple API sources with different formats
- Realistic test data structure
- Easy to extend and modify

### ✅ **Enhanced Architecture**
- DTO pattern for data transfer
- Repository pattern for data access
- Service layer separation
- Dependency injection throughout

## Requirements

- Docker and Docker Compose
- PHP 8.3+ (if running locally)
- Composer (if running locally)

## Setup Instructions

### 1. Clone and Setup

```bash
git clone <your-repo-url>
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

### 5. Load Data Fixtures

```bash
docker-compose exec php bin/console doctrine:fixtures:load --no-interaction
```

### 6. Aggregate Sample Price Data

```bash
# Aggregate prices for all sample products with detailed output
docker-compose exec php bin/console app:aggregate-prices --verbose-output

# Clear cache before aggregation
docker-compose exec php bin/console app:aggregate-prices --clear-cache

# Aggregate for a specific product
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
│   ├── DTO/
│   │   └── Price/          # Data Transfer Objects
│   └── Service/            # Application services (use cases)
├── DataFixtures/           # Database fixtures for test data
├── Domain/
│   ├── Entity/            # Domain entities
│   ├── Repository/        # Repository interfaces
│   ├── Service/           # Domain service interfaces
│   └── ValueObject/       # Value objects
├── Infrastructure/
│   ├── Cache/             # Redis caching implementation
│   ├── Entity/            # Doctrine entities
│   ├── External/          # External API service implementations
│   └── Persistence/       # Repository implementations
└── UI/
    ├── Command/           # Console commands
    ├── Controller/        # API controllers
    └── Security/          # Authentication services
```

## Dynamic API Sources

The service now uses configurable API sources stored in the database:

**API One Format** (Shopping Sites):
```json
{
  "product_id": "123",
  "prices": [
    { "vendor": "ShopA", "price": 19.99 },
    { "vendor": "ShopB", "price": 17.49 }
  ]
}
```

**API Two Format** (Vendor Network):
```json
{
  "id": "123",
  "competitor_data": [
    { "name": "VendorOne", "amount": 20.49 },
    { "name": "VendorTwo", "amount": 18.99 }
  ]
}
```

**API Three Format** (Supplier Network):
```json
{
  "product_id": "123",
  "suppliers": [
    { "supplier": "SupplierAlpha", "cost": 18.25 },
    { "supplier": "SupplierBeta", "cost": 19.75 }
  ]
}
```

## Enhanced Commands

### Price Aggregation Command
```bash
# Basic aggregation
docker-compose exec php bin/console app:aggregate-prices

# Verbose output with detailed results
docker-compose exec php bin/console app:aggregate-prices --verbose-output

# Clear cache before running
docker-compose exec php bin/console app:aggregate-prices --clear-cache

# Process specific products
docker-compose exec php bin/console app:aggregate-prices 123,456

# Single product with detailed output
docker-compose exec php bin/console app:aggregate-prices --single=789 --verbose-output
```

### Database Commands
```bash
# Clear cache
docker-compose exec php bin/console cache:clear

# Reload fixtures
docker-compose exec php bin/console doctrine:fixtures:load --no-interaction

# View logs
docker-compose logs php
```

## Caching Strategy

- **Product Prices**: Cached for 10 minutes
- **API Responses**: Cached for 5 minutes per source
- **All Prices Endpoint**: Cached for 5 minutes
- **Cache Keys**: Structured with prefixes for easy management
- **Invalidation**: Automatic on price updates

## Retry Strategy

- **Max Retries**: 3 attempts (configurable)
- **Base Delay**: 1000ms (configurable)
- **Backoff**: Exponential with 2.0 multiplier
- **Retryable Exceptions**: RuntimeException, JsonException
- **Logging**: Detailed retry attempt logging

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

### Cache Management
```bash
# Clear all cache
docker-compose exec php bin/console app:aggregate-prices --clear-cache

# Redis CLI access
docker-compose exec redis redis-cli
```

## Environment Variables

Key environment variables in `.env`:

- `DATABASE_URL`: MySQL connection string
- `API_KEY`: Secret key for API authentication
- `REDIS_URL`: Redis connection string
- `APP_ENV`: Application environment (dev/prod)

## Performance Features

- **Database Indexing**: Optimized queries with proper indexes
- **Connection Pooling**: Efficient database connection management
- **Memory Management**: Proper resource cleanup and memory usage
- **Async Processing**: Ready for background job integration
- **Monitoring**: Comprehensive logging and error tracking

## Future Enhancements

- Scheduled price updates via cron jobs
- Rate limiting for API endpoints
- Token authentification system
- Health check endpoints
