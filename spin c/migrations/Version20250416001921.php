<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250416001921 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE injury CHANGE injury_description injury_description LONGTEXT NOT NULL, CHANGE user_id user_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE injury ADD CONSTRAINT FK_8A4A592DA76ED395 FOREIGN KEY (user_id) REFERENCES user (user_id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX user_id ON injury
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_8A4A592DA76ED395 ON injury (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE recoveryplan DROP FOREIGN KEY recoveryplan_ibfk_1
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE recoveryplan DROP FOREIGN KEY recoveryplan_ibfk_2
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE recoveryplan DROP FOREIGN KEY recoveryplan_ibfk_1
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE recoveryplan DROP FOREIGN KEY recoveryplan_ibfk_2
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE recoveryplan CHANGE recovery_description recovery_description VARCHAR(255) NOT NULL, CHANGE recovery_Status recovery_Status VARCHAR(255) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE recoveryplan ADD CONSTRAINT FK_D6AA4338ABA45E9A FOREIGN KEY (injury_id) REFERENCES injury (injury_id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE recoveryplan ADD CONSTRAINT FK_D6AA4338A76ED395 FOREIGN KEY (user_id) REFERENCES user (user_id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX injury_id ON recoveryplan
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_D6AA4338ABA45E9A ON recoveryplan (injury_id)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX user_id ON recoveryplan
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_D6AA4338A76ED395 ON recoveryplan (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE recoveryplan ADD CONSTRAINT recoveryplan_ibfk_1 FOREIGN KEY (injury_id) REFERENCES injury (injury_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE recoveryplan ADD CONSTRAINT recoveryplan_ibfk_2 FOREIGN KEY (user_id) REFERENCES user (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX `primary` ON results
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE results ADD CONSTRAINT FK_9FA3E414BFFDBB45 FOREIGN KEY (tournamentId) REFERENCES tournament (tournamentId) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE results ADD CONSTRAINT FK_9FA3E414D8528F51 FOREIGN KEY (teamId) REFERENCES team (teamId) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE results ADD PRIMARY KEY (tournamentId, teamId)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE team CHANGE teamId teamId INT AUTO_INCREMENT NOT NULL, ADD PRIMARY KEY (teamId)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tournament CHANGE tournamentId tournamentId INT AUTO_INCREMENT NOT NULL, ADD PRIMARY KEY (tournamentId)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX user_email ON user
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user CHANGE user_nbr user_nbr VARCHAR(255) NOT NULL, CHANGE isInjured isInjured TINYINT(1) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user ADD CONSTRAINT FK_8D93D64970335B1D FOREIGN KEY (athlete_teamId) REFERENCES team (teamId) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_8D93D64970335B1D ON user (athlete_teamId)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE injury DROP FOREIGN KEY FK_8A4A592DA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE injury DROP FOREIGN KEY FK_8A4A592DA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE injury CHANGE user_id user_id INT NOT NULL, CHANGE injury_description injury_description TEXT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_8a4a592da76ed395 ON injury
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX user_id ON injury (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE injury ADD CONSTRAINT FK_8A4A592DA76ED395 FOREIGN KEY (user_id) REFERENCES user (user_id) ON DELETE CASCADE
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
            ALTER TABLE recoveryplan CHANGE recovery_description recovery_description TEXT DEFAULT NULL, CHANGE recovery_Status recovery_Status VARCHAR(50) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE recoveryplan ADD CONSTRAINT recoveryplan_ibfk_1 FOREIGN KEY (injury_id) REFERENCES injury (injury_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE recoveryplan ADD CONSTRAINT recoveryplan_ibfk_2 FOREIGN KEY (user_id) REFERENCES user (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_d6aa4338a76ed395 ON recoveryplan
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX user_id ON recoveryplan (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_d6aa4338aba45e9a ON recoveryplan
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX injury_id ON recoveryplan (injury_id)
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
            DROP INDEX `PRIMARY` ON results
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE results ADD PRIMARY KEY (teamId)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE team MODIFY teamId INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX `primary` ON team
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE team CHANGE teamId teamId INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tournament MODIFY tournamentId INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX `primary` ON tournament
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tournament CHANGE tournamentId tournamentId INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user DROP FOREIGN KEY FK_8D93D64970335B1D
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_8D93D64970335B1D ON user
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user CHANGE user_nbr user_nbr VARCHAR(50) NOT NULL, CHANGE isInjured isInjured TINYINT(1) DEFAULT 0
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX user_email ON user (user_email)
        SQL);
    }
}
