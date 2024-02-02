<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240201164725 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE stand_participation (id INT AUTO_INCREMENT NOT NULL, stand_id INT DEFAULT NULL, activity_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_332CB1F99734D487 (stand_id), UNIQUE INDEX UNIQ_332CB1F981C06096 (activity_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE stand_participation ADD CONSTRAINT FK_332CB1F99734D487 FOREIGN KEY (stand_id) REFERENCES stand (id)');
        $this->addSql('ALTER TABLE stand_participation ADD CONSTRAINT FK_332CB1F981C06096 FOREIGN KEY (activity_id) REFERENCES activity (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE stand_participation DROP FOREIGN KEY FK_332CB1F99734D487');
        $this->addSql('ALTER TABLE stand_participation DROP FOREIGN KEY FK_332CB1F981C06096');
        $this->addSql('DROP TABLE stand_participation');
    }
}
