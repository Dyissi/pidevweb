<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250408172221 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE claim DROP FOREIGN KEY fk_claim_user
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE claim DROP FOREIGN KEY fk_claim_user_to_claim
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
            ALTER TABLE claim ADD CONSTRAINT fk_claim_user FOREIGN KEY (id_user) REFERENCES user (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE claim ADD CONSTRAINT fk_claim_user_to_claim FOREIGN KEY (id_user_to_claim) REFERENCES user (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE claimaction MODIFY claimActionId INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX `primary` ON claimaction
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE claimaction DROP FOREIGN KEY fk_claimaction_claim
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE claimaction ADD claim_action_id INT NOT NULL, ADD claim_action_start_date DATE NOT NULL, ADD claim_action_end_date DATE NOT NULL, DROP claimActionId, DROP claimActionStartDate, DROP claimActionEndDate, CHANGE claimId claimId INT DEFAULT NULL, CHANGE claimActionType claim_action_type VARCHAR(20) NOT NULL, CHANGE claimActionNotes claim_action_notes VARCHAR(200) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE claimaction ADD PRIMARY KEY (claim_action_id)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX fk_claimaction_claim ON claimaction
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_F1B98B489113A92D ON claimaction (claimId)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE claimaction ADD CONSTRAINT fk_claimaction_claim FOREIGN KEY (claimId) REFERENCES claim (claimId) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE data CHANGE performance_id performance_id INT NOT NULL
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
            ALTER TABLE messenger_messages CHANGE id id BIGINT NOT NULL, CHANGE created_at created_at VARCHAR(255) NOT NULL, CHANGE available_at available_at VARCHAR(255) NOT NULL, CHANGE delivered_at delivered_at VARCHAR(255) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE team MODIFY teamId INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX `primary` ON team
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE team ADD team_id INT NOT NULL, ADD team_name VARCHAR(255) NOT NULL, ADD team_nb_athletes INT NOT NULL, ADD team_type_of_sport VARCHAR(255) NOT NULL, ADD team_wins INT NOT NULL, ADD team_losses INT NOT NULL, DROP teamId, DROP teamName, DROP teamNbAthletes, DROP teamTypeOfSport, DROP teamWins, DROP teamLosses
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE team ADD PRIMARY KEY (team_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tournament MODIFY tournamentId INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX `primary` ON tournament
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tournament ADD tournament_name VARCHAR(255) NOT NULL, ADD tournament_start_date DATE NOT NULL, ADD tournament_end_date DATE NOT NULL, ADD tournament_location VARCHAR(255) NOT NULL, ADD tournament_tos VARCHAR(255) NOT NULL, ADD tournament_nbteams INT NOT NULL, DROP tournamentId, DROP tournamentName, DROP tournamentStartDate, DROP tournamentEndDate, DROP tournamentLocation, DROP tournamentTOS, CHANGE tournamentNbteams tournament_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tournament ADD PRIMARY KEY (tournament_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE training_session MODIFY trainingSession_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX `primary` ON training_session
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE training_session ADD training_session_id INT NOT NULL, DROP trainingSession_id, CHANGE session_start_time session_start_time VARCHAR(255) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE training_session ADD PRIMARY KEY (training_session_id)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX user_email ON user
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user ADD athlete_do_b DATE NOT NULL, ADD is_injured TINYINT(1) NOT NULL, ADD athlete_reg_date DATE NOT NULL, DROP athlete_DoB, DROP isInjured, DROP athlete_regDate, CHANGE user_id user_id INT NOT NULL, CHANGE nb_teams nb_teams INT NOT NULL, CHANGE med_specialty med_specialty VARCHAR(255) NOT NULL, CHANGE athlete_gender athlete_gender VARCHAR(255) NOT NULL, CHANGE athlete_address athlete_address VARCHAR(255) NOT NULL, CHANGE athlete_height athlete_height DOUBLE PRECISION NOT NULL, CHANGE athlete_weight athlete_weight DOUBLE PRECISION NOT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
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
            DROP INDEX `PRIMARY` ON claimaction
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE claimaction DROP FOREIGN KEY FK_F1B98B489113A92D
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE claimaction ADD claimActionId INT AUTO_INCREMENT NOT NULL, ADD claimActionStartDate DATE NOT NULL, ADD claimActionEndDate DATE NOT NULL, DROP claim_action_id, DROP claim_action_start_date, DROP claim_action_end_date, CHANGE claimId claimId INT NOT NULL, CHANGE claim_action_type claimActionType VARCHAR(20) NOT NULL, CHANGE claim_action_notes claimActionNotes VARCHAR(200) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE claimaction ADD PRIMARY KEY (claimActionId)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_f1b98b489113a92d ON claimaction
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX fk_claimaction_claim ON claimaction (claimId)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE claimaction ADD CONSTRAINT FK_F1B98B489113A92D FOREIGN KEY (claimId) REFERENCES claim (claimId) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE data CHANGE performance_id performance_id INT AUTO_INCREMENT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE messenger_messages CHANGE id id BIGINT AUTO_INCREMENT NOT NULL, CHANGE created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', CHANGE available_at available_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', CHANGE delivered_at delivered_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
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
            DROP INDEX `PRIMARY` ON team
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE team ADD teamId INT AUTO_INCREMENT NOT NULL, ADD teamName VARCHAR(255) NOT NULL, ADD teamNbAthletes INT NOT NULL, ADD teamTypeOfSport VARCHAR(255) NOT NULL, ADD teamWins INT NOT NULL, ADD teamLosses INT NOT NULL, DROP team_id, DROP team_name, DROP team_nb_athletes, DROP team_type_of_sport, DROP team_wins, DROP team_losses
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE team ADD PRIMARY KEY (teamId)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX `PRIMARY` ON tournament
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tournament ADD tournamentId INT AUTO_INCREMENT NOT NULL, ADD tournamentName VARCHAR(255) NOT NULL, ADD tournamentStartDate DATE NOT NULL, ADD tournamentEndDate DATE NOT NULL, ADD tournamentLocation VARCHAR(255) NOT NULL, ADD tournamentTOS VARCHAR(255) NOT NULL, ADD tournamentNbteams INT NOT NULL, DROP tournament_id, DROP tournament_name, DROP tournament_start_date, DROP tournament_end_date, DROP tournament_location, DROP tournament_tos, DROP tournament_nbteams
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tournament ADD PRIMARY KEY (tournamentId)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX `PRIMARY` ON training_session
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE training_session ADD trainingSession_id INT AUTO_INCREMENT NOT NULL, DROP training_session_id, CHANGE session_start_time session_start_time TIME NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE training_session ADD PRIMARY KEY (trainingSession_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user ADD athlete_DoB DATE DEFAULT NULL, ADD isInjured TINYINT(1) DEFAULT NULL, ADD athlete_regDate DATE DEFAULT NULL, DROP athlete_do_b, DROP is_injured, DROP athlete_reg_date, CHANGE user_id user_id INT AUTO_INCREMENT NOT NULL, CHANGE nb_teams nb_teams INT DEFAULT NULL, CHANGE med_specialty med_specialty VARCHAR(255) DEFAULT NULL, CHANGE athlete_gender athlete_gender VARCHAR(255) DEFAULT NULL, CHANGE athlete_address athlete_address VARCHAR(255) DEFAULT NULL, CHANGE athlete_height athlete_height DOUBLE PRECISION DEFAULT NULL, CHANGE athlete_weight athlete_weight DOUBLE PRECISION DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX user_email ON user (user_email)
        SQL);
    }
}
