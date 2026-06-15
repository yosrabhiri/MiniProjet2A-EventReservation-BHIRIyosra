<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260615154000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create default events when the production database is empty.';
    }

    public function up(Schema $schema): void
    {
        if ((int) $this->connection->fetchOne('SELECT COUNT(*) FROM event') > 0) {
            return;
        }

        $this->addSql("INSERT INTO event (title, description, date, location, seats, image) VALUES ('Tech Connect', 'Une rencontre pour decouvrir les dernieres tendances tech et reseauter avec des passionnes.', DATE_ADD(NOW(), INTERVAL 7 DAY), 'Tunis', 120, 'https://images.unsplash.com/photo-1519389950473-47ba0277781c?auto=format&fit=crop&w=1200&q=80')");
        $this->addSql("INSERT INTO event (title, description, date, location, seats, image) VALUES ('Music Night', 'Une soiree musicale conviviale avec des artistes locaux et une ambiance festive.', DATE_ADD(NOW(), INTERVAL 12 DAY), 'Sousse', 200, 'https://images.unsplash.com/photo-1501281668745-f7f57925c3b4?auto=format&fit=crop&w=1200&q=80')");
        $this->addSql("INSERT INTO event (title, description, date, location, seats, image) VALUES ('Startup Workshop', 'Atelier pratique pour apprendre a presenter un projet et preparer un pitch efficace.', DATE_ADD(NOW(), INTERVAL 18 DAY), 'Sfax', 80, 'https://images.unsplash.com/photo-1556761175-b413da4baf72?auto=format&fit=crop&w=1200&q=80')");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DELETE FROM event WHERE title IN ('Tech Connect', 'Music Night', 'Startup Workshop')");
    }
}
