<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251201085723 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE promo_code (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(64) NOT NULL, description LONGTEXT DEFAULT NULL, scope_type VARCHAR(255) NOT NULL, discount_percent NUMERIC(5, 2) NOT NULL, is_active TINYINT(1) NOT NULL, starts_at DATETIME DEFAULT NULL, expires_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, section_id INT DEFAULT NULL, category_id INT DEFAULT NULL, product_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_3D8C939E77153098 (code), INDEX IDX_3D8C939ED823E37A (section_id), INDEX IDX_3D8C939E12469DE2 (category_id), INDEX IDX_3D8C939E4584665A (product_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE promo_code ADD CONSTRAINT FK_3D8C939ED823E37A FOREIGN KEY (section_id) REFERENCES section (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE promo_code ADD CONSTRAINT FK_3D8C939E12469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE promo_code ADD CONSTRAINT FK_3D8C939E4584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE promo_code DROP FOREIGN KEY FK_3D8C939ED823E37A');
        $this->addSql('ALTER TABLE promo_code DROP FOREIGN KEY FK_3D8C939E12469DE2');
        $this->addSql('ALTER TABLE promo_code DROP FOREIGN KEY FK_3D8C939E4584665A');
        $this->addSql('DROP TABLE promo_code');
    }
}
