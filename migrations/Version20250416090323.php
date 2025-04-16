<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250416090323 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        
    // 1. Temporarily make nullable
    $this->addSql('ALTER TABLE training_session MODIFY session_location VARCHAR(25) DEFAULT NULL');
    
    // 2. Add temporary column for string values
    $this->addSql('ALTER TABLE training_session ADD old_location VARCHAR(25) DEFAULT NULL');
    $this->addSql('UPDATE training_session SET old_location = session_location');
    
    // 3. Convert column to INT
    $this->addSql('ALTER TABLE training_session MODIFY session_location INT DEFAULT NULL');
    
    // 4. Map string locations to Location IDs
    $this->addSql('UPDATE training_session t 
        JOIN location l ON t.old_location = l.location_name 
        SET t.session_location = l.id');
    
    // 5. Add FK constraint
    $this->addSql('ALTER TABLE training_session ADD CONSTRAINT FK_TRAINING_LOCATION 
        FOREIGN KEY (session_location) REFERENCES location (id)');
    
    // 6. Clean up
    $this->addSql('ALTER TABLE training_session DROP old_location');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE claim (claimId INT NOT NULL, claimDescription VARCHAR(200) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`, claimStatus VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_0900_ai_ci`, claimDate DATE NOT NULL, claimCategory VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_0900_ai_ci`) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = MyISAM COMMENT = '' 
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE claimaction (claimActionId INT AUTO_INCREMENT NOT NULL, claimActionType VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_0900_ai_ci`, claimActionStartDate DATE NOT NULL, claimActionEndDate DATE NOT NULL, claimActionNotes VARCHAR(200) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`, claimId INT NOT NULL, INDEX fk_claim (claimId), PRIMARY KEY(claimActionId)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = MyISAM COMMENT = '' 
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE tournament (tournamentId INT AUTO_INCREMENT NOT NULL, tournamentName VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_0900_ai_ci`, tournamentStartDate DATE DEFAULT NULL, tournamentEndDate DATE DEFAULT NULL, tournamentLocation VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_0900_ai_ci`, tournamentTOS VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_0900_ai_ci`, tournamentNbteams INT DEFAULT NULL, PRIMARY KEY(tournamentId)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = MyISAM COMMENT = '' 
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE nutritionplan (nutrition_id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, nutrition_dietType VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_0900_ai_ci`, nutrition_allergies VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_0900_ai_ci`, nutrition_calorie_intake INT NOT NULL, nutrition_start_date DATE NOT NULL, nutrition_end_date DATE NOT NULL, nutrition_meal_plan TEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`, nutrition_notes TEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`, INDEX fk_user_id (user_id), PRIMARY KEY(nutrition_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = MyISAM COMMENT = '' 
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE team (teamId INT AUTO_INCREMENT NOT NULL, teamName VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`, teamNbAtheletes INT NOT NULL, teamTypeOfSport VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`, teamWins INT NOT NULL, teamLosses INT NOT NULL, PRIMARY KEY(teamId)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = MyISAM COMMENT = '' 
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE recoveryplan (recovery_id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, injury_id INT NOT NULL, recovery_Goal VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_0900_ai_ci`, recovery_Description TEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`, recovery_StartDate DATE NOT NULL, recovery_EndDate DATE NOT NULL, Recovery_Status VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_0900_ai_ci`, INDEX fk_user_id (user_id), PRIMARY KEY(recovery_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = MyISAM COMMENT = '' 
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE injury (injury_id INT AUTO_INCREMENT NOT NULL, injuryType VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_0900_ai_ci`, injuryDate DATE NOT NULL, injury_severity VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_0900_ai_ci`, injury_description TEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`, user_id INT NOT NULL, INDEX fk_user_id (user_id), PRIMARY KEY(injury_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = MyISAM COMMENT = '' 
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE user (user_id INT AUTO_INCREMENT NOT NULL, user_fname VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`, user_lname VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`, user_email VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`, user_pwd VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`, user_nbr VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`, user_role VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_0900_ai_ci`, nb_teams INT DEFAULT NULL, med_specialty VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_0900_ai_ci`, athlete_DoB DATE DEFAULT NULL, athlete_gender VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_0900_ai_ci`, athlete_address VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_0900_ai_ci`, athlete_height NUMERIC(5, 2) DEFAULT NULL, athlete_weight NUMERIC(5, 2) DEFAULT NULL, isInjured TINYINT(1) DEFAULT NULL, PRIMARY KEY(user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = MyISAM COMMENT = '' 
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE training_session DROP FOREIGN KEY FK_D7A45DAD532E9A3
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_D7A45DAD532E9A3 ON training_session
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE training_session CHANGE session_location session_location VARCHAR(25) NOT NULL, CHANGE session_notes session_notes VARCHAR(250) NOT NULL, CHANGE team_id team_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX fk_team ON training_session (team_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE data CHANGE user_id user_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX fk_user ON data (user_id)
        SQL);
    }
}
