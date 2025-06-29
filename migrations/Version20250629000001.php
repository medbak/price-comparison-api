<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250629000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create product_prices table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE product_prices (
            id INT AUTO_INCREMENT NOT NULL,
            product_id VARCHAR(255) NOT NULL,
            vendor_name VARCHAR(255) NOT NULL,
            price NUMERIC(10, 2) NOT NULL,
            fetched_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            PRIMARY KEY(id),
            INDEX idx_product_id (product_id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE product_prices');
    }
}
