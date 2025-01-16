<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250116203354 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE answer (id BIGSERIAL NOT NULL, question_id BIGINT NOT NULL, name TEXT NOT NULL, is_true BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_DADD4A251E27F6BF ON answer (question_id)');
        $this->addSql('CREATE TABLE answer_user (id BIGSERIAL NOT NULL, answer_id BIGINT NOT NULL, user_id BIGINT NOT NULL, name TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_D3B83589AA334807 ON answer_user (answer_id)');
        $this->addSql('CREATE INDEX IDX_D3B83589A76ED395 ON answer_user (user_id)');
        $this->addSql('CREATE TABLE course (id BIGSERIAL NOT NULL, name TEXT NOT NULL, description TEXT DEFAULT NULL, code TEXT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_169E6FB977153098 ON course (code)');
        $this->addSql('CREATE TABLE course_user (id BIGSERIAL NOT NULL, course_id BIGINT NOT NULL, user_id BIGINT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_45310B4F591CC992 ON course_user (course_id)');
        $this->addSql('CREATE INDEX IDX_45310B4FA76ED395 ON course_user (user_id)');
        $this->addSql('CREATE TABLE lesson (id BIGSERIAL NOT NULL, course_id BIGINT NOT NULL, name TEXT NOT NULL, description TEXT DEFAULT NULL, hw_deadline TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, file_path TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F87474F382A8E361 ON lesson (file_path)');
        $this->addSql('CREATE INDEX IDX_F87474F3591CC992 ON lesson (course_id)');
        $this->addSql('CREATE TABLE lesson_user (id BIGSERIAL NOT NULL, user_id BIGINT NOT NULL, lesson_id BIGINT NOT NULL, score INT DEFAULT NULL, file_path TEXT NOT NULL, uploaded_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B4E2102D82A8E361 ON lesson_user (file_path)');
        $this->addSql('CREATE INDEX IDX_B4E2102DA76ED395 ON lesson_user (user_id)');
        $this->addSql('CREATE INDEX IDX_B4E2102DCDF80196 ON lesson_user (lesson_id)');
        $this->addSql('CREATE TABLE question (id BIGSERIAL NOT NULL, test_id BIGINT NOT NULL, name TEXT NOT NULL, type INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_B6F7494E1E5D0459 ON question (test_id)');
        $this->addSql('CREATE TABLE test (id BIGSERIAL NOT NULL, lesson_id BIGINT NOT NULL, name TEXT NOT NULL, deadline TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D87F7E0CCDF80196 ON test (lesson_id)');
        $this->addSql('CREATE TABLE test_user (id BIGSERIAL NOT NULL, test_id BIGINT NOT NULL, user_id BIGINT NOT NULL, score DOUBLE PRECISION DEFAULT NULL, datetime_start TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, datetime_finish TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_88EAFC861E5D0459 ON test_user (test_id)');
        $this->addSql('CREATE INDEX IDX_88EAFC86A76ED395 ON test_user (user_id)');
        $this->addSql('ALTER TABLE answer ADD CONSTRAINT FK_DADD4A251E27F6BF FOREIGN KEY (question_id) REFERENCES question (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE answer_user ADD CONSTRAINT FK_D3B83589AA334807 FOREIGN KEY (answer_id) REFERENCES answer (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE answer_user ADD CONSTRAINT FK_D3B83589A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE course_user ADD CONSTRAINT FK_45310B4F591CC992 FOREIGN KEY (course_id) REFERENCES course (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE course_user ADD CONSTRAINT FK_45310B4FA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE lesson ADD CONSTRAINT FK_F87474F3591CC992 FOREIGN KEY (course_id) REFERENCES course (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE lesson_user ADD CONSTRAINT FK_B4E2102DA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE lesson_user ADD CONSTRAINT FK_B4E2102DCDF80196 FOREIGN KEY (lesson_id) REFERENCES lesson (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE question ADD CONSTRAINT FK_B6F7494E1E5D0459 FOREIGN KEY (test_id) REFERENCES test (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE test ADD CONSTRAINT FK_D87F7E0CCDF80196 FOREIGN KEY (lesson_id) REFERENCES lesson (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE test_user ADD CONSTRAINT FK_88EAFC861E5D0459 FOREIGN KEY (test_id) REFERENCES test (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE test_user ADD CONSTRAINT FK_88EAFC86A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE answer DROP CONSTRAINT FK_DADD4A251E27F6BF');
        $this->addSql('ALTER TABLE answer_user DROP CONSTRAINT FK_D3B83589AA334807');
        $this->addSql('ALTER TABLE answer_user DROP CONSTRAINT FK_D3B83589A76ED395');
        $this->addSql('ALTER TABLE course_user DROP CONSTRAINT FK_45310B4F591CC992');
        $this->addSql('ALTER TABLE course_user DROP CONSTRAINT FK_45310B4FA76ED395');
        $this->addSql('ALTER TABLE lesson DROP CONSTRAINT FK_F87474F3591CC992');
        $this->addSql('ALTER TABLE lesson_user DROP CONSTRAINT FK_B4E2102DA76ED395');
        $this->addSql('ALTER TABLE lesson_user DROP CONSTRAINT FK_B4E2102DCDF80196');
        $this->addSql('ALTER TABLE question DROP CONSTRAINT FK_B6F7494E1E5D0459');
        $this->addSql('ALTER TABLE test DROP CONSTRAINT FK_D87F7E0CCDF80196');
        $this->addSql('ALTER TABLE test_user DROP CONSTRAINT FK_88EAFC861E5D0459');
        $this->addSql('ALTER TABLE test_user DROP CONSTRAINT FK_88EAFC86A76ED395');
        $this->addSql('DROP TABLE answer');
        $this->addSql('DROP TABLE answer_user');
        $this->addSql('DROP TABLE course');
        $this->addSql('DROP TABLE course_user');
        $this->addSql('DROP TABLE lesson');
        $this->addSql('DROP TABLE lesson_user');
        $this->addSql('DROP TABLE question');
        $this->addSql('DROP TABLE test');
        $this->addSql('DROP TABLE test_user');
    }
}
