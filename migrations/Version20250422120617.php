<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250422120617 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE lesson_file_id_seq CASCADE');
        $this->addSql('CREATE TABLE lesson_user_file (id BIGSERIAL NOT NULL, lesson_user_id BIGINT NOT NULL, name TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_923D955352128132 ON lesson_user_file (lesson_user_id)');
        $this->addSql('ALTER TABLE lesson_user_file ADD CONSTRAINT FK_923D955352128132 FOREIGN KEY (lesson_user_id) REFERENCES lesson_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
    }
}
