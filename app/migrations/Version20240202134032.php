<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240202134032 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE activity ADD global_duration DATETIME DEFAULT NULL, ADD rotation_duration DATETIME DEFAULT NULL, ADD stand_duration DATETIME DEFAULT NULL, DROP global_time, DROP rotation_time, DROP stand_time');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE activity ADD global_time DATETIME DEFAULT NULL, ADD rotation_time DATETIME DEFAULT NULL, ADD stand_time DATETIME DEFAULT NULL, DROP global_duration, DROP rotation_duration, DROP stand_duration');
    }
}
