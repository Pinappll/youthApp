<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250131095932 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE event ADD target_church_id INT DEFAULT NULL, ADD scope VARCHAR(20) NOT NULL');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA7F5E7FDF8 FOREIGN KEY (target_church_id) REFERENCES church (id)');
        $this->addSql('CREATE INDEX IDX_3BAE0AA7F5E7FDF8 ON event (target_church_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA7F5E7FDF8');
        $this->addSql('DROP INDEX IDX_3BAE0AA7F5E7FDF8 ON event');
        $this->addSql('ALTER TABLE event DROP target_church_id, DROP scope');
    }
}
