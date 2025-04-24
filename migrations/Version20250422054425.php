<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250422054425 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Добавляем табличку с файлами и ссылку на видео в Lesson';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE lesson_file (id BIGSERIAL NOT NULL, lesson_id BIGINT NOT NULL, name TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_B5EEF074CDF80196 ON lesson_file (lesson_id)');
        $this->addSql('ALTER TABLE lesson_file ADD CONSTRAINT FK_B5EEF074CDF80196 FOREIGN KEY (lesson_id) REFERENCES lesson (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('DROP INDEX uniq_f87474f382a8e361');
        $this->addSql('ALTER TABLE lesson RENAME COLUMN file_path TO video');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE lesson_file DROP CONSTRAINT FK_B5EEF074CDF80196');
        $this->addSql('DROP TABLE lesson_file');
        $this->addSql('ALTER TABLE lesson RENAME COLUMN video TO file_path');
        $this->addSql('CREATE UNIQUE INDEX uniq_f87474f382a8e361 ON lesson (file_path)');
    }
}
