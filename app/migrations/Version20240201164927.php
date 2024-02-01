<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240201164927 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE stand ADD animator_id_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE stand ADD CONSTRAINT FK_64B918B6BF2F113A FOREIGN KEY (animator_id_id) REFERENCES animator (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_64B918B6BF2F113A ON stand (animator_id_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE stand DROP FOREIGN KEY FK_64B918B6BF2F113A');
        $this->addSql('DROP INDEX UNIQ_64B918B6BF2F113A ON stand');
        $this->addSql('ALTER TABLE stand DROP animator_id_id');
    }
}
