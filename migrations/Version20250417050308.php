<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250409_Consolidated extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Consolidated migration to sync database with entity mappings as of April 2025, excluding nutritionplan, keeping athlete_teamId, data.user_id, and training_session.team_id nullable, fixing messenger_messages.id schema, and removing redundant userId and teamId columns';
    }

    public function up(Schema $schema): void
    {
        // Ensure messenger_messages table has correct schema
        $this->addSql('DROP TABLE IF EXISTS messenger_messages');
        $this->addSql(<<<'SQL'
            CREATE TABLE messenger_messages (
                id BIGINT NOT NULL AUTO_INCREMENT,
                body LONGTEXT NOT NULL,
                headers LONGTEXT NOT NULL,
                queue_name VARCHAR(190) NOT NULL,
                created_at VARCHAR(255) NOT NULL,
                available_at VARCHAR(255) NOT NULL,
                delivered_at VARCHAR(255) NOT NULL,
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);

        // claim: Fix foreign keys
        $this->addSql('ALTER TABLE claim DROP FOREIGN KEY IF EXISTS fk_claim_user');
        $this->addSql('ALTER TABLE claim DROP FOREIGN KEY IF EXISTS fk_claim_user_to_claim');
        $this->addSql('ALTER TABLE claim DROP FOREIGN KEY IF EXISTS FK_A769DE276B3CA4B');
        $this->addSql('ALTER TABLE claim DROP FOREIGN KEY IF EXISTS FK_A769DE27F2F0DA72');
        $this->addSql('DROP INDEX IF EXISTS fk_claim_user ON claim');
        $this->addSql('DROP INDEX IF EXISTS fk_claim_user_to_claim ON claim');
        $this->addSql('DROP INDEX IF EXISTS IDX_A769DE276B3CA4B ON claim');
        $this->addSql('DROP INDEX IF EXISTS IDX_A769DE27F2F0DA72 ON claim');
        $this->addSql('ALTER TABLE claim CHANGE id_user id_user INT DEFAULT NULL, CHANGE id_user_to_claim id_user_to_claim INT DEFAULT NULL');
        $this->addSql('CREATE INDEX IF NOT EXISTS IDX_A769DE276B3CA4B ON claim (id_user)');
        $this->addSql('CREATE INDEX IF NOT EXISTS IDX_A769DE27F2F0DA72 ON claim (id_user_to_claim)');
        $this->addSql('ALTER TABLE claim ADD CONSTRAINT FK_A769DE276B3CA4B FOREIGN KEY (id_user) REFERENCES user (user_id)');
        $this->addSql('ALTER TABLE claim ADD CONSTRAINT FK_A769DE27F2F0DA72 FOREIGN KEY (id_user_to_claim) REFERENCES user (user_id)');
        $this->addSql('ALTER TABLE claim CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

        // claimaction: Fix index and foreign key
        $this->addSql('ALTER TABLE claimaction DROP FOREIGN KEY IF EXISTS FK_F1B98B489113A92D');
        $this->addSql('DROP INDEX IF EXISTS IDX_F1B98B489113A92D ON claimaction');
        $this->addSql('DROP INDEX IF EXISTS fk_f1b98b489113a92d ON claimaction');
        $this->addSql('ALTER TABLE claimaction CHANGE claimActionStartDate claimActionStartDate DATE NOT NULL, CHANGE claimActionEndDate claimActionEndDate DATE NOT NULL, CHANGE claimActionNotes claimActionNotes VARCHAR(200) NOT NULL');
        $this->addSql('CREATE INDEX IF NOT EXISTS IDX_F1B98B489113A92D ON claimaction (claimId)');
        $this->addSql('ALTER TABLE claimaction ADD CONSTRAINT FK_F1B98B489113A92D FOREIGN KEY (claimId) REFERENCES claim (claimId) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE claimaction CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

        // data: Keep user_id NULLABLE, drop redundant userId column
        $this->addSql('ALTER TABLE data DROP FOREIGN KEY IF EXISTS fk_user');
        $this->addSql('ALTER TABLE data DROP FOREIGN KEY IF EXISTS FK_DATA_USER');
        $this->addSql('ALTER TABLE data DROP FOREIGN KEY IF EXISTS FK_ADF3F363A76ED395');
        $this->addSql('DROP INDEX IF EXISTS fk_user ON data');
        $this->addSql('DROP INDEX IF EXISTS idx_data_user ON data');
        $this->addSql('DROP INDEX IF EXISTS IDX_ADF3F363A76ED395 ON data');
        $this->addSql('ALTER TABLE data DROP COLUMN IF EXISTS userId, CHANGE user_id user_id INT DEFAULT NULL, CHANGE performance_id performance_id INT AUTO_INCREMENT NOT NULL');
        $this->addSql('CREATE INDEX IF NOT EXISTS IDX_ADF3F363A76ED395 ON data (user_id)');
        $this->addSql('ALTER TABLE data ADD CONSTRAINT FK_ADF3F363A76ED395 FOREIGN KEY (user_id) REFERENCES user (user_id)');
        $this->addSql('ALTER TABLE data CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

        // doctrine_migration_versions: Fix column constraints
        $this->addSql('ALTER TABLE doctrine_migration_versions CHANGE executed_at executed_at DATETIME NOT NULL, CHANGE execution_time execution_time INT NOT NULL');
        $this->addSql('ALTER TABLE doctrine_migration_versions CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci');

        // injury: Fix foreign key with ON DELETE CASCADE
        $this->addSql('ALTER TABLE injury DROP FOREIGN KEY IF EXISTS FK_8A4A592DA76ED395');
        $this->addSql('DROP INDEX IF EXISTS FK_8A4A592DA76ED395 ON injury');
        $this->addSql('DROP INDEX IF EXISTS fk_8a4a592da76ed395 ON injury');
        $this->addSql('DROP INDEX IF EXISTS IDX_8A4A592DA76ED395 ON injury');
        $this->addSql('ALTER TABLE injury CHANGE user_id user_id INT DEFAULT NULL, CHANGE injuryType injuryType VARCHAR(50) NOT NULL, CHANGE injury_severity injury_severity VARCHAR(255) NOT NULL, CHANGE injury_description injury_description VARCHAR(255) NOT NULL, CHANGE injury_date injury_date DATE NOT NULL');
        $this->addSql('CREATE INDEX IF NOT EXISTS IDX_8A4A592DA76ED395 ON injury (user_id)');
        $this->addSql('ALTER TABLE injury ADD CONSTRAINT FK_8A4A592DA76ED395 FOREIGN KEY (user_id) REFERENCES user (user_id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE injury CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

        // location: Fix collation
        $this->addSql('ALTER TABLE location CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

        // recoveryplan: Fix foreign keys
        $this->addSql('ALTER TABLE recoveryplan DROP FOREIGN KEY IF EXISTS recoveryplan_ibfk_1');
        $this->addSql('ALTER TABLE recoveryplan DROP FOREIGN KEY IF EXISTS recoveryplan_ibfk_2');
        $this->addSql('ALTER TABLE recoveryplan DROP FOREIGN KEY IF EXISTS FK_D6AA4338A76ED395');
        $this->addSql('ALTER TABLE recoveryplan DROP FOREIGN KEY IF EXISTS FK_D6AA4338ABA45E9A');
        $this->addSql('DROP INDEX IF EXISTS recoveryplan_ibfk_1 ON recoveryplan');
        $this->addSql('DROP INDEX IF EXISTS recoveryplan_ibfk_2 ON recoveryplan');
        $this->addSql('DROP INDEX IF EXISTS IDX_D6AA4338ABA45E9A ON recoveryplan');
        $this->addSql('DROP INDEX IF EXISTS IDX_D6AA4338A76ED395 ON recoveryplan');
        $this->addSql('ALTER TABLE recoveryplan CHANGE user_id user_id INT DEFAULT NULL, CHANGE injury_id injury_id INT DEFAULT NULL, CHANGE recovery_description recovery_description VARCHAR(255) NOT NULL, CHANGE recovery_status recovery_status VARCHAR(255) NOT NULL');
        $this->addSql('CREATE INDEX IF NOT EXISTS IDX_D6AA4338ABA45E9A ON recoveryplan (injury_id)');
        $this->addSql('CREATE INDEX IF NOT EXISTS IDX_D6AA4338A76ED395 ON recoveryplan (user_id)');
        $this->addSql('ALTER TABLE recoveryplan ADD CONSTRAINT FK_D6AA4338ABA45E9A FOREIGN KEY (injury_id) REFERENCES injury (injury_id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE recoveryplan ADD CONSTRAINT FK_D6AA4338A76ED395 FOREIGN KEY (user_id) REFERENCES user (user_id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE recoveryplan CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

        // results: Fix indexes and foreign keys
        $this->addSql('ALTER TABLE results DROP FOREIGN KEY IF EXISTS FK_9FA3E414D8528F51');
        $this->addSql('ALTER TABLE results DROP FOREIGN KEY IF EXISTS FK_9FA3E414BFFDBB45');
        $this->addSql('DROP INDEX IF EXISTS FK_9FA3E414D8528F51 ON results');
        $this->addSql('DROP INDEX IF EXISTS fk_9fa3e414d8528f51 ON results');
        $this->addSql('DROP INDEX IF EXISTS IDX_9FA3E414D8528F51 ON results');
        $this->addSql('CREATE INDEX IF NOT EXISTS IDX_9FA3E414D8528F51 ON results (teamId)');
        $this->addSql('ALTER TABLE results ADD CONSTRAINT FK_9FA3E414D8528F51 FOREIGN KEY (teamId) REFERENCES team (teamId) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE results ADD CONSTRAINT FK_9FA3E414BFFDBB45 FOREIGN KEY (tournamentId) REFERENCES tournament (tournamentId) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE results CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

        // team: Fix collation
        $this->addSql('ALTER TABLE team CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

        // tournament: Fix column constraints
        $this->addSql('ALTER TABLE tournament CHANGE tournamentWinner tournamentWinner INT DEFAULT NULL');
        $this->addSql('ALTER TABLE tournament CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

        // training_session: Keep team_id NULLABLE, drop redundant teamId column
        $this->addSql('ALTER TABLE training_session DROP FOREIGN KEY IF EXISTS FK_TRAINING_LOCATION');
        $this->addSql('ALTER TABLE training_session DROP FOREIGN KEY IF EXISTS fk_team');
        $this->addSql('ALTER TABLE training_session DROP FOREIGN KEY IF EXISTS FK_D7A45DAD532E9A3');
        $this->addSql('ALTER TABLE training_session DROP FOREIGN KEY IF EXISTS FK_D7A45DA296CD8AE');
        $this->addSql('DROP INDEX IF EXISTS fk_team ON training_session');
        $this->addSql('DROP INDEX IF EXISTS FK_TRAINING_LOCATION ON training_session');
        $this->addSql('DROP INDEX IF EXISTS idx_d7a45dad296cd8ae ON training_session');
        $this->addSql('DROP INDEX IF EXISTS IDX_D7A45DAD532E9A3 ON training_session');
        $this->addSql('DROP INDEX IF EXISTS IDX_D7A45DA296CD8AE ON training_session');
        $this->addSql('ALTER TABLE training_session DROP COLUMN IF EXISTS teamId, CHANGE session_id session_id INT AUTO_INCREMENT NOT NULL, CHANGE team_id team_id INT DEFAULT NULL, CHANGE session_start_time session_start_time TIME NOT NULL, CHANGE session_notes session_notes VARCHAR(1250) NOT NULL');
        $this->addSql('CREATE INDEX IF NOT EXISTS IDX_D7A45DAD532E9A3 ON training_session (session_location)');
        $this->addSql('CREATE INDEX IF NOT EXISTS IDX_D7A45DA296CD8AE ON training_session (team_id)');
        $this->addSql('ALTER TABLE training_session ADD CONSTRAINT FK_D7A45DAD532E9A3 FOREIGN KEY (session_location) REFERENCES location (id)');
        $this->addSql('ALTER TABLE training_session ADD CONSTRAINT FK_D7A45DA296CD8AE FOREIGN KEY (team_id) REFERENCES team (teamId)');
        $this->addSql('ALTER TABLE training_session CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

        // user: Fix foreign key with ON DELETE CASCADE
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY IF EXISTS FK_8D93D64970335B1D');
        $this->addSql('DROP INDEX IF EXISTS FK_8D93D64970335B1D ON user');
        $this->addSql('DROP INDEX IF EXISTS fk_8d93d64970335b1d ON user');
        $this->addSql('DROP INDEX IF EXISTS IDX_8D93D64970335B1D ON user');
        $this->addSql('ALTER TABLE user CHANGE user_id user_id INT AUTO_INCREMENT NOT NULL, CHANGE user_role user_role VARCHAR(50) NOT NULL, CHANGE nb_teams nb_teams INT DEFAULT NULL, CHANGE athlete_gender athlete_gender VARCHAR(50) DEFAULT NULL, CHANGE isInjured isInjured TINYINT(1) DEFAULT NULL, CHANGE athlete_teamId athlete_teamId INT DEFAULT NULL');
        $this->addSql('CREATE INDEX IF NOT EXISTS IDX_8D93D64970335B1D ON user (athlete_teamId)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D64970335B1D FOREIGN KEY (athlete_teamId) REFERENCES team (teamId) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    }

    public function down(Schema $schema): void
    {
        // Drop messenger_messages
        $this->addSql('DROP TABLE IF EXISTS messenger_messages');

        // claim: Revert
        $this->addSql('ALTER TABLE claim DROP FOREIGN KEY IF EXISTS FK_A769DE276B3CA4B');
        $this->addSql('ALTER TABLE claim DROP FOREIGN KEY IF EXISTS FK_A769DE27F2F0DA72');
        $this->addSql('DROP INDEX IF EXISTS IDX_A769DE276B3CA4B ON claim');
        $this->addSql('DROP INDEX IF EXISTS IDX_A769DE27F2F0DA72 ON claim');
        $this->addSql('ALTER TABLE claim CHANGE id_user id_user INT NOT NULL, CHANGE id_user_to_claim id_user_to_claim INT NOT NULL');
        $this->addSql('CREATE INDEX fk_claim_user ON claim (id_user)');
        $this->addSql('CREATE INDEX fk_claim_user_to_claim ON claim (id_user_to_claim)');
        $this->addSql('ALTER TABLE claim ADD CONSTRAINT fk_claim_user FOREIGN KEY (id_user) REFERENCES user (user_id)');
        $this->addSql('ALTER TABLE claim ADD CONSTRAINT fk_claim_user_to_claim FOREIGN KEY (id_user_to_claim) REFERENCES user (user_id)');
        $this->addSql('ALTER TABLE claim CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci');

        // claimaction: Revert
        $this->addSql('ALTER TABLE claimaction DROP FOREIGN KEY IF EXISTS FK_F1B98B489113A92D');
        $this->addSql('DROP INDEX IF EXISTS IDX_F1B98B489113A92D ON claimaction');
        $this->addSql('ALTER TABLE claimaction CHANGE claimActionStartDate claimActionStartDate DATE DEFAULT NULL, CHANGE claimActionEndDate claimActionEndDate DATE DEFAULT NULL, CHANGE claimActionNotes claimActionNotes VARCHAR(200) NOT NULL');
        $this->addSql('CREATE INDEX fk_f1b98b489113a92d ON claimaction (claimId)');
        $this->addSql('ALTER TABLE claimaction ADD CONSTRAINT fk_f1b98b489113a92d FOREIGN KEY (claimId) REFERENCES claim (claimId) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE claimaction CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci');

        // data: Revert, restore userId column
        $this->addSql('ALTER TABLE data DROP FOREIGN KEY IF EXISTS FK_ADF3F363A76ED395');
        $this->addSql('DROP INDEX IF EXISTS IDX_ADF3F363A76ED395 ON data');
        $this->addSql('ALTER TABLE data ADD userId INT NOT NULL, CHANGE user_id user_id INT NOT NULL, CHANGE performance_id performance_id INT AUTO_INCREMENT NOT NULL');
        $this->addSql('CREATE INDEX fk_user ON data (user_id)');
        $this->addSql('ALTER TABLE data ADD CONSTRAINT fk_user FOREIGN KEY (user_id) REFERENCES user (user_id)');
        $this->addSql('ALTER TABLE data CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci');

        // doctrine_migration_versions: Revert
        $this->addSql('ALTER TABLE doctrine_migration_versions CHANGE executed_at executed_at DATETIME DEFAULT NULL, CHANGE execution_time execution_time INT DEFAULT NULL');
        $this->addSql('ALTER TABLE doctrine_migration_versions CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci');

        // injury: Revert
        $this->addSql('ALTER TABLE injury DROP FOREIGN KEY IF EXISTS FK_8A4A592DA76ED395');
        $this->addSql('DROP INDEX IF EXISTS IDX_8A4A592DA76ED395 ON injury');
        $this->addSql('ALTER TABLE injury CHANGE user_id user_id INT NOT NULL, CHANGE injuryType injuryType VARCHAR(50) NOT NULL, CHANGE injury_severity injury_severity VARCHAR(255) NOT NULL, CHANGE injury_description injury_description VARCHAR(255) NOT NULL, CHANGE injury_date injury_date DATE NOT NULL');
        $this->addSql('CREATE INDEX fk_8a4a592da76ed395 ON injury (user_id)');
        $this->addSql('ALTER TABLE injury ADD CONSTRAINT fk_8a4a592da76ed395 FOREIGN KEY (user_id) REFERENCES user (user_id)');
        $this->addSql('ALTER TABLE injury CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci');

        // location: Revert
        $this->addSql('ALTER TABLE location CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci');

        // recoveryplan: Revert
        $this->addSql('ALTER TABLE recoveryplan DROP FOREIGN KEY IF EXISTS FK_D6AA4338ABA45E9A');
        $this->addSql('ALTER TABLE recoveryplan DROP FOREIGN KEY IF EXISTS FK_D6AA4338A76ED395');
        $this->addSql('DROP INDEX IF EXISTS IDX_D6AA4338ABA45E9A ON recoveryplan');
        $this->addSql('DROP INDEX IF EXISTS IDX_D6AA4338A76ED395 ON recoveryplan');
        $this->addSql('ALTER TABLE recoveryplan CHANGE user_id user_id INT NOT NULL, CHANGE injury_id injury_id INT NOT NULL, CHANGE recovery_description recovery_description VARCHAR(255) NOT NULL, CHANGE recovery_status recovery_status VARCHAR(255) NOT NULL');
        $this->addSql('CREATE INDEX recoveryplan_ibfk_1 ON recoveryplan (injury_id)');
        $this->addSql('CREATE INDEX recoveryplan_ibfk_2 ON recoveryplan (user_id)');
        $this->addSql('ALTER TABLE recoveryplan ADD CONSTRAINT recoveryplan_ibfk_1 FOREIGN KEY (injury_id) REFERENCES injury (injury_id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE recoveryplan ADD CONSTRAINT recoveryplan_ibfk_2 FOREIGN KEY (user_id) REFERENCES user (user_id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE recoveryplan CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci');

        // results: Revert
        $this->addSql('ALTER TABLE results DROP FOREIGN KEY IF EXISTS FK_9FA3E414D8528F51');
        $this->addSql('ALTER TABLE results DROP FOREIGN KEY IF EXISTS FK_9FA3E414BFFDBB45');
        $this->addSql('DROP INDEX IF EXISTS IDX_9FA3E414D8528F51 ON results');
        $this->addSql('CREATE INDEX fk_9fa3e414d8528f51 ON results (teamId)');
        $this->addSql('ALTER TABLE results ADD CONSTRAINT fk_9fa3e414d8528f51 FOREIGN KEY (teamId) REFERENCES team (teamId)');
        $this->addSql('ALTER TABLE results ADD CONSTRAINT fk_9fa3e414bffDBB45 FOREIGN KEY (tournamentId) REFERENCES tournament (tournamentId)');
        $this->addSql('ALTER TABLE results CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci');

        // team: Revert
        $this->addSql('ALTER TABLE team CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci');

        // tournament: Revert
        $this->addSql('ALTER TABLE tournament CHANGE tournamentWinner tournamentWinner INT NOT NULL');
        $this->addSql('ALTER TABLE tournament CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci');

        // training_session: Revert, restore teamId column
        $this->addSql('ALTER TABLE training_session DROP FOREIGN KEY IF EXISTS FK_D7A45DAD532E9A3');
        $this->addSql('ALTER TABLE training_session DROP FOREIGN KEY IF EXISTS FK_D7A45DA296CD8AE');
        $this->addSql('DROP INDEX IF EXISTS IDX_D7A45DAD532E9A3 ON training_session');
        $this->addSql('DROP INDEX IF EXISTS IDX_D7A45DA296CD8AE ON training_session');
        $this->addSql('ALTER TABLE training_session ADD teamId INT NOT NULL, CHANGE session_id session_id INT AUTO_INCREMENT NOT NULL, CHANGE team_id team_id INT NOT NULL, CHANGE session_start_time session_start_time TIME NOT NULL, CHANGE session_notes session_notes VARCHAR(1250) NOT NULL');
        $this->addSql('CREATE INDEX fk_team ON training_session (team_id)');
        $this->addSql('CREATE INDEX FK_TRAINING_LOCATION ON training_session (session_location)');
        $this->addSql('ALTER TABLE training_session ADD CONSTRAINT fk_team FOREIGN KEY (team_id) REFERENCES team (teamId)');
        $this->addSql('ALTER TABLE training_session ADD CONSTRAINT FK_TRAINING_LOCATION FOREIGN KEY (session_location) REFERENCES location (id)');
        $this->addSql('ALTER TABLE training_session CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci');

        // user: Revert
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY IF EXISTS FK_8D93D64970335B1D');
        $this->addSql('DROP INDEX IF EXISTS IDX_8D93D64970335B1D ON user');
        $this->addSql('ALTER TABLE user CHANGE user_id user_id INT AUTO_INCREMENT NOT NULL, CHANGE user_role user_role VARCHAR(50) NOT NULL, CHANGE nb_teams nb_teams INT NOT NULL, CHANGE athlete_gender athlete_gender VARCHAR(50) NOT NULL, CHANGE isInjured isInjured TINYINT(1) NOT NULL, CHANGE athlete_teamId athlete_teamId INT NOT NULL');
        $this->addSql('CREATE INDEX fk_8d93d64970335b1d ON user (athlete_teamId)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT fk_8d93d64970335b1d FOREIGN KEY (athlete_teamId) REFERENCES team (teamId)');
        $this->addSql('ALTER TABLE user CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci');
    }
}