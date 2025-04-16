<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250409200805 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE injury CHANGE injury_description injury_description LONGTEXT NOT NULL, CHANGE user_id user_id INT DEFAULT NULL, CHANGE injuryDate injury_date DATE NOT NULL
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
            ALTER TABLE recoveryplan ADD recovery_start_date DATE NOT NULL, ADD recovery_end_date DATE NOT NULL, DROP recovery_StartDate, DROP recovery_EndDate, CHANGE recovery_id recovery_id INT NOT NULL, CHANGE recovery_Description recovery_description LONGTEXT NOT NULL
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
            DROP INDEX user_email ON user
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user CHANGE user_id user_id INT NOT NULL
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
            ALTER TABLE injury CHANGE user_id user_id INT NOT NULL, CHANGE injury_description injury_description TEXT NOT NULL, CHANGE injuryDate injury_date DATE NOT NULL
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
            ALTER TABLE recoveryplan ADD recovery_StartDate DATE NOT NULL, ADD recovery_EndDate DATE NOT NULL, DROP recovery_start_date, DROP recovery_end_date, CHANGE recovery_id recovery_id INT AUTO_INCREMENT NOT NULL, CHANGE recovery_description recovery_Description TEXT DEFAULT NULL
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
            ALTER TABLE user CHANGE user_id user_id INT AUTO_INCREMENT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX user_email ON user (user_email)
        SQL);
    }
}
