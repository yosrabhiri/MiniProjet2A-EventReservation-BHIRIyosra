<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260615150000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create the default admin account when it is missing.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO admin (username, roles, password) VALUES (\'admin\', \'["ROLE_ADMIN"]\', \'$2y$10$BhaSDrIqR4jz0OfAqeCb3OoxIrgL1JZZvcgADSVpTtRmMp8C3/3Qy\')');
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DELETE FROM admin WHERE username = 'admin'");
    }
}
