<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250416210220 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE nutritionplan (nutrition_id INT NOT NULL, user_id INT NOT NULL, nutrition_dietType VARCHAR(255) NOT NULL, nutrition_allergies VARCHAR(255) NOT NULL, nutrition_calorie_intake INT NOT NULL, nutrition_start_date DATE NOT NULL, nutrition_end_date DATE NOT NULL, nutrition_meal_plan LONGTEXT NOT NULL, nutrition_notes LONGTEXT NOT NULL, PRIMARY KEY(nutrition_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE performance_data (performance_id INT NOT NULL, performance_speed DOUBLE PRECISION NOT NULL, performance_agility DOUBLE PRECISION NOT NULL, performance_nbr_goals INT NOT NULL, performance_assists INT NOT NULL, performance_date_recorded DATE NOT NULL, performance_nbr_fouls INT NOT NULL, PRIMARY KEY(performance_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE claim ADD CONSTRAINT FK_A769DE276B3CA4B FOREIGN KEY (id_user) REFERENCES user (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE claim ADD CONSTRAINT FK_A769DE27F2F0DA72 FOREIGN KEY (id_user_to_claim) REFERENCES user (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX fk_claim_user ON claim
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_A769DE276B3CA4B ON claim (id_user)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX fk_claim_user_to_claim ON claim
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_A769DE27F2F0DA72 ON claim (id_user_to_claim)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE claimaction ADD CONSTRAINT FK_F1B98B489113A92D FOREIGN KEY (claimId) REFERENCES claim (claimId) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX fk_user ON data
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE data CHANGE performance_id performance_id INT AUTO_INCREMENT NOT NULL, CHANGE user_id user_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE injury CHANGE injury_id injury_id INT AUTO_INCREMENT NOT NULL, CHANGE injuryType injuryType VARCHAR(50) NOT NULL, CHANGE injury_description injury_description LONGTEXT NOT NULL, CHANGE user_id user_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE injury ADD CONSTRAINT FK_8A4A592DA76ED395 FOREIGN KEY (user_id) REFERENCES user (user_id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE location CHANGE id id INT AUTO_INCREMENT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_75EA56E0E3BD61CE ON messenger_messages
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_75EA56E016BA31DB ON messenger_messages
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_75EA56E0FB7336F0 ON messenger_messages
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE messenger_messages CHANGE created_at created_at VARCHAR(255) NOT NULL, CHANGE available_at available_at VARCHAR(255) NOT NULL, CHANGE delivered_at delivered_at VARCHAR(255) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE recoveryplan CHANGE recovery_id recovery_id INT AUTO_INCREMENT NOT NULL, CHANGE user_id user_id INT DEFAULT NULL, CHANGE injury_id injury_id INT DEFAULT NULL, CHANGE recovery_Description recovery_description VARCHAR(255) NOT NULL, CHANGE Recovery_Status recovery_Status VARCHAR(255) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE recoveryplan ADD CONSTRAINT FK_D6AA4338ABA45E9A FOREIGN KEY (injury_id) REFERENCES injury (injury_id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE recoveryplan ADD CONSTRAINT FK_D6AA4338A76ED395 FOREIGN KEY (user_id) REFERENCES user (user_id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX recoveryplan_ibfk_1 ON recoveryplan
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_D6AA4338ABA45E9A ON recoveryplan (injury_id)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX recoveryplan_ibfk_2 ON recoveryplan
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_D6AA4338A76ED395 ON recoveryplan (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE results ADD CONSTRAINT FK_9FA3E414BFFDBB45 FOREIGN KEY (tournamentId) REFERENCES tournament (tournamentId) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE results ADD CONSTRAINT FK_9FA3E414D8528F51 FOREIGN KEY (teamId) REFERENCES team (teamId) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE team CHANGE teamId teamId INT AUTO_INCREMENT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tournament CHANGE tournamentId tournamentId INT AUTO_INCREMENT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX fk_team ON training_session
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE training_session ADD session_id INT AUTO_INCREMENT NOT NULL, DROP trainingSession_id, CHANGE session_notes session_notes VARCHAR(1250) NOT NULL, CHANGE team_id team_id INT NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (session_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE training_session ADD CONSTRAINT FK_D7A45DAD532E9A3 FOREIGN KEY (session_location) REFERENCES location (id)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX fk_training_location ON training_session
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_D7A45DAD532E9A3 ON training_session (session_location)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user CHANGE user_id user_id INT AUTO_INCREMENT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user ADD CONSTRAINT FK_8D93D64970335B1D FOREIGN KEY (athlete_teamId) REFERENCES team (teamId) ON DELETE CASCADE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP TABLE nutritionplan
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE performance_data
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE claim DROP FOREIGN KEY FK_A769DE276B3CA4B
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE claim DROP FOREIGN KEY FK_A769DE27F2F0DA72
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE claim DROP FOREIGN KEY FK_A769DE276B3CA4B
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE claim DROP FOREIGN KEY FK_A769DE27F2F0DA72
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_a769de276b3ca4b ON claim
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX fk_claim_user ON claim (id_user)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_a769de27f2f0da72 ON claim
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX fk_claim_user_to_claim ON claim (id_user_to_claim)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE claim ADD CONSTRAINT FK_A769DE276B3CA4B FOREIGN KEY (id_user) REFERENCES user (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE claim ADD CONSTRAINT FK_A769DE27F2F0DA72 FOREIGN KEY (id_user_to_claim) REFERENCES user (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE claimaction DROP FOREIGN KEY FK_F1B98B489113A92D
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE data CHANGE performance_id performance_id INT NOT NULL, CHANGE user_id user_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX fk_user ON data (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE injury DROP FOREIGN KEY FK_8A4A592DA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE injury CHANGE injury_id injury_id INT NOT NULL, CHANGE user_id user_id INT NOT NULL, CHANGE injuryType injuryType VARCHAR(255) NOT NULL, CHANGE injury_description injury_description TEXT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE location CHANGE id id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE messenger_messages CHANGE created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', CHANGE available_at available_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', CHANGE delivered_at delivered_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE recoveryplan DROP FOREIGN KEY FK_D6AA4338ABA45E9A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE recoveryplan DROP FOREIGN KEY FK_D6AA4338A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE recoveryplan DROP FOREIGN KEY FK_D6AA4338ABA45E9A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE recoveryplan DROP FOREIGN KEY FK_D6AA4338A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE recoveryplan CHANGE recovery_id recovery_id INT NOT NULL, CHANGE injury_id injury_id INT NOT NULL, CHANGE user_id user_id INT NOT NULL, CHANGE recovery_description recovery_Description TEXT DEFAULT NULL, CHANGE recovery_Status Recovery_Status VARCHAR(50) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_d6aa4338aba45e9a ON recoveryplan
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX recoveryplan_ibfk_1 ON recoveryplan (injury_id)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_d6aa4338a76ed395 ON recoveryplan
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX recoveryplan_ibfk_2 ON recoveryplan (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE recoveryplan ADD CONSTRAINT FK_D6AA4338ABA45E9A FOREIGN KEY (injury_id) REFERENCES injury (injury_id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE recoveryplan ADD CONSTRAINT FK_D6AA4338A76ED395 FOREIGN KEY (user_id) REFERENCES user (user_id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE results DROP FOREIGN KEY FK_9FA3E414BFFDBB45
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE results DROP FOREIGN KEY FK_9FA3E414D8528F51
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE team CHANGE teamId teamId INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tournament CHANGE tournamentId tournamentId INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE training_session MODIFY session_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE training_session DROP FOREIGN KEY FK_D7A45DAD532E9A3
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX `PRIMARY` ON training_session
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE training_session DROP FOREIGN KEY FK_D7A45DAD532E9A3
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE training_session ADD trainingSession_id INT NOT NULL, DROP session_id, CHANGE session_notes session_notes VARCHAR(250) NOT NULL, CHANGE team_id team_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX fk_team ON training_session (team_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE training_session ADD PRIMARY KEY (trainingSession_id)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_d7a45dad532e9a3 ON training_session
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX FK_TRAINING_LOCATION ON training_session (session_location)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE training_session ADD CONSTRAINT FK_D7A45DAD532E9A3 FOREIGN KEY (session_location) REFERENCES location (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user DROP FOREIGN KEY FK_8D93D64970335B1D
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user CHANGE user_id user_id INT NOT NULL
        SQL);
    }
}
