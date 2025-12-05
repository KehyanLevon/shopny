<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251204105335 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Backfill created_at in user table and set NOT NULL';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('UPDATE `user` SET created_at = NOW() WHERE created_at IS NULL');

        $this->addSql('ALTER TABLE `user` CHANGE created_at created_at DATETIME NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `user` CHANGE created_at created_at DATETIME NULL');
    }
}
