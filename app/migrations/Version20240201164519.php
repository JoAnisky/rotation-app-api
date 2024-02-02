<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240201164519 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE team_participation (id INT AUTO_INCREMENT NOT NULL, team_id INT DEFAULT NULL, activity_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_DE37C7DE296CD8AE (team_id), UNIQUE INDEX UNIQ_DE37C7DE81C06096 (activity_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE team_participation ADD CONSTRAINT FK_DE37C7DE296CD8AE FOREIGN KEY (team_id) REFERENCES team (id)');
        $this->addSql('ALTER TABLE team_participation ADD CONSTRAINT FK_DE37C7DE81C06096 FOREIGN KEY (activity_id) REFERENCES activity (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE team_participation DROP FOREIGN KEY FK_DE37C7DE296CD8AE');
        $this->addSql('ALTER TABLE team_participation DROP FOREIGN KEY FK_DE37C7DE81C06096');
        $this->addSql('DROP TABLE team_participation');
    }
}
