<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250409044328 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Consolidated migration syncing database with entity mappings without destructive changes or duplicate constraints';
    }

    public function up(Schema $schema): void
    {
        // Create messenger_messages table (safe with IF NOT EXISTS)
        $this->addSql(<<<'SQL'
            CREATE TABLE IF NOT EXISTS messenger_messages (
                id BIGINT AUTO_INCREMENT NOT NULL,
                body LONGTEXT NOT NULL,
                headers LONGTEXT NOT NULL,
                queue_name VARCHAR(190) NOT NULL,
                created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                available_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                delivered_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                INDEX IDX_75EA56E0FB7336F0 (queue_name),
                INDEX IDX_75EA56E0E3BD61CE (available_at),
                INDEX IDX_75EA56E016BA31DB (delivered_at),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB
        SQL);

        // claimaction: Fix claimId, add foreign key if not exists
        $this->addSql('ALTER TABLE claimaction CHANGE claimId claimId INT NOT NULL'); // Matches DB
        $this->addSql('CREATE INDEX IF NOT EXISTS IDX_F1B98B489113A92D ON claimaction (claimId)');
        // Check if FK_F1B98B489113A92D exists before adding
        $this->addSql('SET @fk_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = "claimaction" AND CONSTRAINT_NAME = "FK_F1B98B489113A92D" AND CONSTRAINT_TYPE = "FOREIGN KEY")');
        $this->addSql('SET @sql = IF(@fk_exists = 0, "ALTER TABLE claimaction ADD CONSTRAINT FK_F1B98B489113A92D FOREIGN KEY (claimId) REFERENCES claim (claimId) ON DELETE CASCADE", "SELECT \"FK_F1B98B489113A92D already exists\"")');
        $this->addSql('PREPARE stmt FROM @sql');
        $this->addSql('EXECUTE stmt');
        $this->addSql('DEALLOCATE PREPARE stmt');

        // injury: Ensure index only (foreign key already exists)
        $this->addSql('CREATE INDEX IF NOT EXISTS IDX_8A4A592DA76ED395 ON injury (user_id)');

        // results: Ensure indexes only (foreign keys already exist)
        $this->addSql('CREATE INDEX IF NOT EXISTS IDX_9FA3E414D8528F51 ON results (teamId)');
        $this->addSql('CREATE INDEX IF NOT EXISTS IDX_9FA3E414BFFDBB45 ON results (tournamentId)');

        // user: Ensure index only (foreign key already exists)
        $this->addSql('CREATE INDEX IF NOT EXISTS IDX_8D93D64970335B1D ON user (athlete_teamId)');

        // Minimal charset conversions for other tables
        $this->addSql('ALTER TABLE nutritionplan CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE performance_data CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE recoveryplan CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE tournament CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE training_session CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    }

    public function down(Schema $schema): void
    {
        // Drop messenger_messages
        $this->addSql('DROP TABLE IF EXISTS messenger_messages');

        // claimaction: Revert claimId and drop added foreign key if it was added
        $this->addSql('SET @fk_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = "claimaction" AND CONSTRAINT_NAME = "FK_F1B98B489113A92D" AND CONSTRAINT_TYPE = "FOREIGN KEY")');
        $this->addSql('SET @sql = IF(@fk_exists > 0, "ALTER TABLE claimaction DROP FOREIGN KEY FK_F1B98B489113A92D", "SELECT \"FK_F1B98B489113A92D not found\"")');
        $this->addSql('PREPARE stmt FROM @sql');
        $this->addSql('EXECUTE stmt');
        $this->addSql('DEALLOCATE PREPARE stmt');
        $this->addSql('ALTER TABLE claimaction CHANGE claimId claimId INT NOT NULL');

        // No other drops since existing constraints/indexes are not modified
    }
}