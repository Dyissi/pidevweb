<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250506040742 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE team DROP FOREIGN KEY fk_teamCoachid
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX fk_teamcoachid ON team
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_C4E0A61F7EF1F90C ON team (coachId)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE team ADD CONSTRAINT fk_teamCoachid FOREIGN KEY (coachId) REFERENCES user (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE training_session CHANGE session_start_time session_start_time DATETIME NOT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE team DROP FOREIGN KEY FK_C4E0A61F7EF1F90C
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_c4e0a61f7ef1f90c ON team
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX fk_teamCoachid ON team (coachId)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE team ADD CONSTRAINT FK_C4E0A61F7EF1F90C FOREIGN KEY (coachId) REFERENCES user (user_id) ON DELETE RESTRICT
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE training_session CHANGE session_start_time session_start_time TIME NOT NULL
        SQL);
    }
}
