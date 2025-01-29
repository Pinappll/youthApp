<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250129214626 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE attendance (id INT AUTO_INCREMENT NOT NULL, event_id INT NOT NULL, youth_id INT NOT NULL, created_by_id INT NOT NULL, is_present TINYINT(1) NOT NULL, comment LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, last_modified_at DATETIME DEFAULT NULL, INDEX IDX_6DE30D9171F7E88B (event_id), INDEX IDX_6DE30D919BEC706D (youth_id), INDEX IDX_6DE30D91B03A8386 (created_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE church (id INT AUTO_INCREMENT NOT NULL, sector_id INT NOT NULL, name VARCHAR(255) NOT NULL, address VARCHAR(255) NOT NULL, INDEX IDX_90CDDD45DE95C867 (sector_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE event (id INT AUTO_INCREMENT NOT NULL, sector_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, date DATETIME NOT NULL, location VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, last_modified_at DATETIME DEFAULT NULL, INDEX IDX_3BAE0AA7DE95C867 (sector_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sector (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, sector_id INT DEFAULT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), INDEX IDX_8D93D649DE95C867 (sector_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE youth (id INT AUTO_INCREMENT NOT NULL, church_id INT NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, address VARCHAR(255) NOT NULL, photo VARCHAR(255) DEFAULT NULL, birth_date DATE NOT NULL, phone VARCHAR(20) NOT NULL, INDEX IDX_BA56DD5C1538FD4 (church_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE attendance ADD CONSTRAINT FK_6DE30D9171F7E88B FOREIGN KEY (event_id) REFERENCES event (id)');
        $this->addSql('ALTER TABLE attendance ADD CONSTRAINT FK_6DE30D919BEC706D FOREIGN KEY (youth_id) REFERENCES youth (id)');
        $this->addSql('ALTER TABLE attendance ADD CONSTRAINT FK_6DE30D91B03A8386 FOREIGN KEY (created_by_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE church ADD CONSTRAINT FK_90CDDD45DE95C867 FOREIGN KEY (sector_id) REFERENCES sector (id)');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA7DE95C867 FOREIGN KEY (sector_id) REFERENCES sector (id)');
        $this->addSql('ALTER TABLE `user` ADD CONSTRAINT FK_8D93D649DE95C867 FOREIGN KEY (sector_id) REFERENCES sector (id)');
        $this->addSql('ALTER TABLE youth ADD CONSTRAINT FK_BA56DD5C1538FD4 FOREIGN KEY (church_id) REFERENCES church (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE attendance DROP FOREIGN KEY FK_6DE30D9171F7E88B');
        $this->addSql('ALTER TABLE attendance DROP FOREIGN KEY FK_6DE30D919BEC706D');
        $this->addSql('ALTER TABLE attendance DROP FOREIGN KEY FK_6DE30D91B03A8386');
        $this->addSql('ALTER TABLE church DROP FOREIGN KEY FK_90CDDD45DE95C867');
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA7DE95C867');
        $this->addSql('ALTER TABLE `user` DROP FOREIGN KEY FK_8D93D649DE95C867');
        $this->addSql('ALTER TABLE youth DROP FOREIGN KEY FK_BA56DD5C1538FD4');
        $this->addSql('DROP TABLE attendance');
        $this->addSql('DROP TABLE church');
        $this->addSql('DROP TABLE event');
        $this->addSql('DROP TABLE sector');
        $this->addSql('DROP TABLE `user`');
        $this->addSql('DROP TABLE youth');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
