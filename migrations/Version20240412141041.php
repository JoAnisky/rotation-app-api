<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240412141041 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE stand DROP FOREIGN KEY FK_64B918B670FBD26D');
        $this->addSql('ALTER TABLE stand DROP FOREIGN KEY FK_64B918B681C06096');
        $this->addSql('ALTER TABLE stand DROP FOREIGN KEY FK_64B918B6A76ED395');
        $this->addSql('DROP INDEX IDX_64B918B6A76ED395 ON stand');
        $this->addSql('DROP INDEX UNIQ_64B918B670FBD26D ON stand');
        $this->addSql('DROP INDEX IDX_64B918B681C06096 ON stand');
        $this->addSql('ALTER TABLE stand DROP animator_id, DROP activity_id, DROP user_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE stand ADD animator_id INT DEFAULT NULL, ADD activity_id INT DEFAULT NULL, ADD user_id INT NOT NULL');
        $this->addSql('ALTER TABLE stand ADD CONSTRAINT FK_64B918B670FBD26D FOREIGN KEY (animator_id) REFERENCES animator (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE stand ADD CONSTRAINT FK_64B918B681C06096 FOREIGN KEY (activity_id) REFERENCES activity (id) ON UPDATE NO ACTION ON DELETE SET NULL');
        $this->addSql('ALTER TABLE stand ADD CONSTRAINT FK_64B918B6A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_64B918B6A76ED395 ON stand (user_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_64B918B670FBD26D ON stand (animator_id)');
        $this->addSql('CREATE INDEX IDX_64B918B681C06096 ON stand (activity_id)');
    }
}
