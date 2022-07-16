<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220716120139 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE ap_inbox_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE ap_outbox_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE ap_inbox (id INT NOT NULL, body JSON NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN ap_inbox.created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('CREATE TABLE ap_outbox (id INT NOT NULL, user_id INT NOT NULL, magazine_id INT DEFAULT NULL, type VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_FF9FD54A76ED395 ON ap_outbox (user_id)');
        $this->addSql('CREATE INDEX IDX_FF9FD543EB84A1D ON ap_outbox (magazine_id)');
        $this->addSql('COMMENT ON COLUMN ap_outbox.created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('ALTER TABLE ap_outbox ADD CONSTRAINT FK_FF9FD54A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE ap_outbox ADD CONSTRAINT FK_FF9FD543EB84A1D FOREIGN KEY (magazine_id) REFERENCES magazine (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE ap_inbox_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE ap_outbox_id_seq CASCADE');
        $this->addSql('DROP TABLE ap_inbox');
        $this->addSql('DROP TABLE ap_outbox');
    }
}
