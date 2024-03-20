<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240305104522 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE activity ADD activity_start_time BIGINT DEFAULT NULL, CHANGE global_duration global_duration INT DEFAULT NULL, CHANGE rotation_duration rotation_duration INT DEFAULT NULL, CHANGE stand_duration stand_duration INT DEFAULT NULL');
        $this->addSql('ALTER TABLE stand DROP FOREIGN KEY FK_64B918B681C06096');
        $this->addSql('ALTER TABLE stand ADD CONSTRAINT FK_64B918B681C06096 FOREIGN KEY (activity_id) REFERENCES activity (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE team DROP FOREIGN KEY FK_C4E0A61F81C06096');
        $this->addSql('ALTER TABLE team ADD CONSTRAINT FK_C4E0A61F81C06096 FOREIGN KEY (activity_id) REFERENCES activity (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE activity DROP activity_start_time, CHANGE global_duration global_duration DATETIME DEFAULT NULL, CHANGE rotation_duration rotation_duration DATETIME DEFAULT NULL, CHANGE stand_duration stand_duration DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE team DROP FOREIGN KEY FK_C4E0A61F81C06096');
        $this->addSql('ALTER TABLE team ADD CONSTRAINT FK_C4E0A61F81C06096 FOREIGN KEY (activity_id) REFERENCES activity (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE stand DROP FOREIGN KEY FK_64B918B681C06096');
        $this->addSql('ALTER TABLE stand ADD CONSTRAINT FK_64B918B681C06096 FOREIGN KEY (activity_id) REFERENCES activity (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
