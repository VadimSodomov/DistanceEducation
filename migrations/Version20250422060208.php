<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250422060208 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Добавление директории для хранения файлов с урока';
    }

    public function up(Schema $schema): void
    {
        // Получаем путь к директории из параметров (можно заменить на конкретный путь)
        $uploadDir = __DIR__ . '/../public/uploads/lesson';

        $filesystem = new Filesystem();

        if (!$filesystem->exists($uploadDir)) {
            $filesystem->mkdir($uploadDir);
        }

        // Для Windows chmod не работает, поэтому проверяем ОС
        if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
            $filesystem->chmod($uploadDir, 0777, 0000, true);
        }

    }

    public function down(Schema $schema): void
    {
    }
}
