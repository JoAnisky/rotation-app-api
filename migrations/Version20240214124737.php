<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240214124737 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE activity (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, name VARCHAR(255) NOT NULL, activity_date DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', statut JSON NOT NULL, nb_participants INT DEFAULT NULL, nb_teams INT DEFAULT NULL, global_duration DATETIME DEFAULT NULL, rotation_duration DATETIME DEFAULT NULL, stand_duration DATETIME DEFAULT NULL, INDEX IDX_AC74095AA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE animator (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_60BF9208A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE stand (id INT AUTO_INCREMENT NOT NULL, animator_id INT DEFAULT NULL, activity_id INT DEFAULT NULL, user_id INT NOT NULL, name VARCHAR(255) NOT NULL, is_competitive TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_64B918B670FBD26D (animator_id), INDEX IDX_64B918B681C06096 (activity_id), INDEX IDX_64B918B6A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE team (id INT AUTO_INCREMENT NOT NULL, activity_id INT DEFAULT NULL, user_id INT NOT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_C4E0A61F81C06096 (activity_id), INDEX IDX_C4E0A61FA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, login VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, roles JSON NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE activity ADD CONSTRAINT FK_AC74095AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE animator ADD CONSTRAINT FK_60BF9208A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE stand ADD CONSTRAINT FK_64B918B670FBD26D FOREIGN KEY (animator_id) REFERENCES animator (id)');
        $this->addSql('ALTER TABLE stand ADD CONSTRAINT FK_64B918B681C06096 FOREIGN KEY (activity_id) REFERENCES activity (id)');
        $this->addSql('ALTER TABLE stand ADD CONSTRAINT FK_64B918B6A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE team ADD CONSTRAINT FK_C4E0A61F81C06096 FOREIGN KEY (activity_id) REFERENCES activity (id)');
        $this->addSql('ALTER TABLE team ADD CONSTRAINT FK_C4E0A61FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE activity DROP FOREIGN KEY FK_AC74095AA76ED395');
        $this->addSql('ALTER TABLE animator DROP FOREIGN KEY FK_60BF9208A76ED395');
        $this->addSql('ALTER TABLE stand DROP FOREIGN KEY FK_64B918B670FBD26D');
        $this->addSql('ALTER TABLE stand DROP FOREIGN KEY FK_64B918B681C06096');
        $this->addSql('ALTER TABLE stand DROP FOREIGN KEY FK_64B918B6A76ED395');
        $this->addSql('ALTER TABLE team DROP FOREIGN KEY FK_C4E0A61F81C06096');
        $this->addSql('ALTER TABLE team DROP FOREIGN KEY FK_C4E0A61FA76ED395');
        $this->addSql('DROP TABLE activity');
        $this->addSql('DROP TABLE animator');
        $this->addSql('DROP TABLE stand');
        $this->addSql('DROP TABLE team');
        $this->addSql('DROP TABLE user');
    }
}
