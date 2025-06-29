<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250629000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create api_sources and product_prices tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE api_sources (
            id INT AUTO_INCREMENT NOT NULL,
            name VARCHAR(255) NOT NULL,
            base_url VARCHAR(500) NOT NULL,
            mock_data JSON NOT NULL,
            response_format VARCHAR(50) NOT NULL,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            timeout_seconds INT NOT NULL DEFAULT 30,
            UNIQUE INDEX UNIQ_NAME (name),
            INDEX idx_active (is_active),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Product Prices table - stores the lowest prices per product
        $this->addSql('CREATE TABLE product_prices (
            id INT AUTO_INCREMENT NOT NULL,
            product_id VARCHAR(255) NOT NULL,
            vendor_name VARCHAR(255) NOT NULL,
            price NUMERIC(10, 2) NOT NULL,
            fetched_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            UNIQUE INDEX UNIQ_PRODUCT (product_id),
            INDEX idx_vendor (vendor_name),
            INDEX idx_fetched_at (fetched_at),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE product_prices');
        $this->addSql('DROP TABLE api_sources');
    }
}
