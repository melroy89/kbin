<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220614170438 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE entry RENAME COLUMN tags TO tags_tmp');
        $this->addSql('ALTER TABLE entry_comment RENAME COLUMN tags TO tags_tmp');
        $this->addSql('ALTER TABLE post RENAME COLUMN tags TO tags_tmp');
        $this->addSql('ALTER TABLE post_comment RENAME COLUMN tags TO tags_tmp');

        $this->addSql('ALTER TABLE entry ADD tags JSONB DEFAULT NULL');
        $this->addSql('ALTER TABLE entry_comment ADD tags JSONB DEFAULT NULL');
        $this->addSql('ALTER TABLE post ADD tags JSONB DEFAULT NULL');
        $this->addSql('ALTER TABLE post_comment ADD tags JSONB DEFAULT NULL');

//        $this->addSql('ALTER TABLE entry DROP tags_tmp');
//        $this->addSql('ALTER TABLE entry_comment DROP tags_tmp');
//        $this->addSql('ALTER TABLE post DROP tags_tmp');
//        $this->addSql('ALTER TABLE post_comment DROP tags_tmp');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE entry DROP tags');
        $this->addSql('ALTER TABLE entry_comment DROP tags');
        $this->addSql('ALTER TABLE post DROP tags');
        $this->addSql('ALTER TABLE post_comment DROP tags');

        $this->addSql('ALTER TABLE entry RENAME COLUMN tags_tmp TO tags');
        $this->addSql('ALTER TABLE entry_comment RENAME COLUMN tags_tmp TO tags');
        $this->addSql('ALTER TABLE post RENAME COLUMN tags_tmp TO tags');
        $this->addSql('ALTER TABLE post_comment RENAME COLUMN tags_tmp TO tags');
    }
}
