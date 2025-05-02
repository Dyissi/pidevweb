<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250427230824 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {// Convert time to datetime while preserving time data
    $this->addSql("ALTER TABLE training_session ADD temp_datetime DATETIME");
    $this->addSql("UPDATE training_session SET temp_datetime = CONCAT('2024-09-01 ', session_start_time)");
    $this->addSql("ALTER TABLE training_session DROP COLUMN session_start_time");
    $this->addSql("ALTER TABLE training_session CHANGE temp_datetime session_start_time DATETIME NOT NULL");

    }

    public function down(Schema $schema): void
    {
        // Revert back to TIME type
        $this->addSql("ALTER TABLE training_session CHANGE session_start_time session_start_time TIME NOT NULL");
    }
}