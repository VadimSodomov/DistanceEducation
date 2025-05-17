<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250517205958 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $uploadDir = __DIR__ . '/../public/uploads/lesson-user';

        $filesystem = new Filesystem();

        if (!$filesystem->exists($uploadDir)) {
            $filesystem->mkdir($uploadDir);
        }

        if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
            $filesystem->chmod($uploadDir, 0777, 0000, true);
        }

    }

    public function down(Schema $schema): void
    {
    }
}
