<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250409_Consolidated extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Consolidated migration reflecting all schema changes up to April 2025, syncing database with entity mappings';
    }

    public function up(Schema $schema): void
    {
        // Create messenger_messages table
        $this->addSql(<<<'SQL'
            CREATE TABLE messenger_messages (
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
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);

        // claimaction: Fix index and foreign key
        $this->addSql('ALTER TABLE claimaction DROP FOREIGN KEY IF EXISTS FK_F1B98B489113A92D');
        $this->addSql('DROP INDEX IF EXISTS FK_F1B98B489113A92D ON claimaction');
        $this->addSql('DROP INDEX IF EXISTS fk_f1b98b489113a92d ON claimaction');
        $this->addSql('ALTER TABLE claimaction CHANGE claimActionStartDate claimActionStartDate DATE NOT NULL, CHANGE claimActionEndDate claimActionEndDate DATE NOT NULL, CHANGE claimActionNotes claimActionNotes VARCHAR(200) NOT NULL');
        $this->addSql('CREATE INDEX IDX_F1B98B489113A92D ON claimaction (claimId)');
        $this->addSql('ALTER TABLE claimaction ADD CONSTRAINT FK_F1B98B489113A92D FOREIGN KEY (claimId) REFERENCES claim (claimId) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE claimaction CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

        // injury: Fix index and foreign key
        $this->addSql('ALTER TABLE injury DROP FOREIGN KEY IF EXISTS FK_8A4A592DA76ED395');
        $this->addSql('DROP INDEX IF EXISTS FK_8A4A592DA76ED395 ON injury');
        $this->addSql('DROP INDEX IF EXISTS fk_8a4a592da76ed395 ON injury');
        $this->addSql('ALTER TABLE injury CHANGE injury_severity injury_severity VARCHAR(50) NOT NULL, CHANGE injury_description injury_description LONGTEXT NOT NULL, CHANGE injuryDate injuryDate DATE NOT NULL');
        $this->addSql('CREATE INDEX IDX_8A4A592DA76ED395 ON injury (user_id)');
        $this->addSql('ALTER TABLE injury ADD CONSTRAINT FK_8A4A592DA76ED395 FOREIGN KEY (user_id) REFERENCES user (user_id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE injury CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

        // nutritionplan: Final column constraints
        $this->addSql('ALTER TABLE nutritionplan CHANGE nutrition_id nutrition_id INT NOT NULL, CHANGE nutrition_meal_plan nutrition_meal_plan LONGTEXT NOT NULL, CHANGE nutrition_notes nutrition_notes LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE nutritionplan CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

        // performance_data: Final column constraints
        $this->addSql('ALTER TABLE performance_data CHANGE performance_id performance_id INT NOT NULL');
        $this->addSql('ALTER TABLE performance_data CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

        // recoveryplan: Final column constraints
        $this->addSql('ALTER TABLE recoveryplan CHANGE recovery_id recovery_id INT NOT NULL, CHANGE recovery_Description recovery_Description LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE recoveryplan CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

        // results: Fix indexes and foreign keys
        $this->addSql('ALTER TABLE results DROP FOREIGN KEY IF EXISTS FK_9FA3E414D8528F51');
        $this->addSql('ALTER TABLE results DROP FOREIGN KEY IF EXISTS FK_9FA3E414BFFDBB45');
        $this->addSql('DROP INDEX IF EXISTS FK_9FA3E414D8528F51 ON results');
        $this->addSql('DROP INDEX IF EXISTS fk_9fa3e414d8528f51 ON results');
        $this->addSql('CREATE INDEX IDX_9FA3E414D8528F51 ON results (teamId)');
        $this->addSql('ALTER TABLE results ADD CONSTRAINT FK_9FA3E414D8528F51 FOREIGN KEY (teamId) REFERENCES team (teamId) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE results ADD CONSTRAINT FK_9FA3E414BFFDBB45 FOREIGN KEY (tournamentId) REFERENCES tournament (tournamentId) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE results CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

        // tournament: Final column constraints
        $this->addSql('ALTER TABLE tournament CHANGE tournamentWinner tournamentWinner INT NOT NULL');
        $this->addSql('ALTER TABLE tournament CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

        // training_session: Final column constraints
        $this->addSql('ALTER TABLE training_session CHANGE trainingSession_id trainingSession_id INT NOT NULL, CHANGE session_start_time session_start_time VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE training_session CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

        // user: Fix index and foreign key
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY IF EXISTS FK_8D93D64970335B1D');
        $this->addSql('DROP INDEX IF EXISTS FK_8D93D64970335B1D ON user');
        $this->addSql('DROP INDEX IF EXISTS fk_8d93d64970335b1d ON user');
        $this->addSql('ALTER TABLE user CHANGE user_id user_id INT AUTO_INCREMENT NOT NULL, CHANGE user_role user_role VARCHAR(50) NOT NULL, CHANGE nb_teams nb_teams INT NOT NULL, CHANGE athlete_gender athlete_gender VARCHAR(50) DEFAULT NULL, CHANGE isInjured isInjured TINYINT(1) NOT NULL, CHANGE athlete_teamId athlete_teamId INT NOT NULL');
        $this->addSql('CREATE INDEX IDX_8D93D64970335B1D ON user (athlete_teamId)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D64970335B1D FOREIGN KEY (athlete_teamId) REFERENCES team (teamId) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    }

    public function down(Schema $schema): void
    {
        // Drop messenger_messages
        $this->addSql('DROP TABLE messenger_messages');

        // claimaction: Revert
        $this->addSql('ALTER TABLE claimaction DROP FOREIGN KEY FK_F1B98B489113A92D');
        $this->addSql('DROP INDEX IDX_F1B98B489113A92D ON claimaction');
        $this->addSql('ALTER TABLE claimaction CHANGE claimActionStartDate claimActionStartDate DATE DEFAULT NULL, CHANGE claimActionEndDate claimActionEndDate DATE DEFAULT NULL, CHANGE claimActionNotes claimActionNotes VARCHAR(200) NOT NULL');
        $this->addSql('ALTER TABLE claimaction ADD CONSTRAINT FK_F1B98B489113A92D FOREIGN KEY (claimId) REFERENCES claim (claimId) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('CREATE INDEX FK_F1B98B489113A92D ON claimaction (claimId)');
        $this->addSql('ALTER TABLE claimaction CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci');

        // injury: Revert
        $this->addSql('ALTER TABLE injury DROP FOREIGN KEY FK_8A4A592DA76ED395');
        $this->addSql('DROP INDEX IDX_8A4A592DA76ED395 ON injury');
        $this->addSql('ALTER TABLE injury CHANGE injury_severity injury_severity VARCHAR(255) NOT NULL, CHANGE injury_description injury_description TEXT NOT NULL, CHANGE injuryDate injuryDate DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE injury ADD CONSTRAINT FK_8A4A592DA76ED395 FOREIGN KEY (user_id) REFERENCES user (user_id) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('CREATE INDEX FK_8A4A592DA76ED395 ON injury (user_id)');
        $this->addSql('ALTER TABLE injury CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci');

        // nutritionplan: Revert
        $this->addSql('ALTER TABLE nutritionplan CHANGE nutrition_id nutrition_id INT AUTO_INCREMENT NOT NULL, CHANGE nutrition_meal_plan nutrition_meal_plan TEXT NOT NULL, CHANGE nutrition_notes nutrition_notes TEXT NOT NULL');
        $this->addSql('ALTER TABLE nutritionplan CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci');

        // performance_data: Revert
        $this->addSql('ALTER TABLE performance_data CHANGE performance_id performance_id INT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE performance_data CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci');

        // recoveryplan: Revert
        $this->addSql('ALTER TABLE recoveryplan CHANGE recovery_id recovery_id INT AUTO_INCREMENT NOT NULL, CHANGE recovery_Description recovery_Description TEXT NOT NULL');
        $this->addSql('ALTER TABLE recoveryplan CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci');

        // results: Revert
        $this->addSql('ALTER TABLE results DROP FOREIGN KEY FK_9FA3E414D8528F51');
        $this->addSql('ALTER TABLE results DROP FOREIGN KEY FK_9FA3E414BFFDBB45');
        $this->addSql('DROP INDEX IDX_9FA3E414D8528F51 ON results');
        $this->addSql('ALTER TABLE results ADD CONSTRAINT FK_9FA3E414D8528F51 FOREIGN KEY (teamId) REFERENCES team (teamId) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('ALTER TABLE results ADD CONSTRAINT FK_9FA3E414BFFDBB45 FOREIGN KEY (tournamentId) REFERENCES tournament (tournamentId) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('CREATE INDEX FK_9FA3E414D8528F51 ON results (teamId)');
        $this->addSql('ALTER TABLE results CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci');

        // tournament: Revert
        $this->addSql('ALTER TABLE tournament CHANGE tournamentWinner tournamentWinner INT DEFAULT NULL');
        $this->addSql('ALTER TABLE tournament CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci');

        // training_session: Revert
        $this->addSql('ALTER TABLE training_session CHANGE trainingSession_id trainingSession_id INT AUTO_INCREMENT NOT NULL, CHANGE session_start_time session_start_time TIME NOT NULL');
        $this->addSql('ALTER TABLE training_session CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci');

        // user: Revert
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D64970335B1D');
        $this->addSql('DROP INDEX IDX_8D93D64970335B1D ON user');
        $this->addSql('ALTER TABLE user CHANGE user_id user_id INT AUTO_INCREMENT NOT NULL, CHANGE user_role user_role VARCHAR(255) NOT NULL, CHANGE nb_teams nb_teams INT DEFAULT NULL, CHANGE athlete_gender athlete_gender VARCHAR(255) DEFAULT NULL, CHANGE isInjured isInjured TINYINT(1) DEFAULT NULL, CHANGE athlete_teamId athlete_teamId INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D64970335B1D FOREIGN KEY (athlete_teamId) REFERENCES team (teamId) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('CREATE INDEX FK_8D93D64970335B1D ON user (athlete_teamId)');
        $this->addSql('ALTER TABLE user CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci');
    }
}