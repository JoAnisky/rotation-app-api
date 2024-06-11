<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240506092919 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // // this up() migration is auto-generated, please modify it to your needs
        // $this->addSql('ALTER TABLE activity ADD participant_code VARCHAR(6) NOT NULL, ADD animator_code VARCHAR(6) NOT NULL');
        // $this->addSql('CREATE UNIQUE INDEX UNIQ_AC74095AA3A122A6 ON activity (participant_code)');
        // $this->addSql('CREATE UNIQUE INDEX UNIQ_AC74095AA6F6F0E7 ON activity (animator_code)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_AC74095AA3A122A6 ON activity');
        $this->addSql('DROP INDEX UNIQ_AC74095AA6F6F0E7 ON activity');
        $this->addSql('ALTER TABLE activity ADD nb_participants INT DEFAULT NULL, DROP participant_code, DROP animator_code');
    }
}
