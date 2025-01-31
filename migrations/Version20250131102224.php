<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250131102224 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE event ADD target_sector_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA7EA21BA4B FOREIGN KEY (target_sector_id) REFERENCES sector (id)');
        $this->addSql('CREATE INDEX IDX_3BAE0AA7EA21BA4B ON event (target_sector_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA7EA21BA4B');
        $this->addSql('DROP INDEX IDX_3BAE0AA7EA21BA4B ON event');
        $this->addSql('ALTER TABLE event DROP target_sector_id');
    }
}
