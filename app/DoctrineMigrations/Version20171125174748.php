<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171125174748 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE backup_job_parameters (id INT AUTO_INCREMENT NOT NULL, backup_job_id INT DEFAULT NULL, backup_parameter_id INT DEFAULT NULL, value TINYTEXT NOT NULL, INDEX IDX_767ED7424BC66710 (backup_job_id), INDEX IDX_767ED7427881A93C (backup_parameter_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE backup_types (id INT AUTO_INCREMENT NOT NULL, key_name VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_A01256DAD824A5CF (key_name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE backup_jobs (id INT AUTO_INCREMENT NOT NULL, server_id INT DEFAULT NULL, backup_type_id INT DEFAULT NULL, INDEX IDX_3393E8591844E6B7 (server_id), INDEX IDX_3393E85977B1A192 (backup_type_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE backup_parameters (id INT AUTO_INCREMENT NOT NULL, backup_type_id INT DEFAULT NULL, key_name VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_E477F635D824A5CF (key_name), INDEX IDX_E477F63577B1A192 (backup_type_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE backup_job_parameters ADD CONSTRAINT FK_767ED7424BC66710 FOREIGN KEY (backup_job_id) REFERENCES backup_jobs (id)');
        $this->addSql('ALTER TABLE backup_job_parameters ADD CONSTRAINT FK_767ED7427881A93C FOREIGN KEY (backup_parameter_id) REFERENCES backup_parameters (id)');
        $this->addSql('ALTER TABLE backup_jobs ADD CONSTRAINT FK_3393E8591844E6B7 FOREIGN KEY (server_id) REFERENCES servers (id)');
        $this->addSql('ALTER TABLE backup_jobs ADD CONSTRAINT FK_3393E85977B1A192 FOREIGN KEY (backup_type_id) REFERENCES backup_types (id)');
        $this->addSql('ALTER TABLE backup_parameters ADD CONSTRAINT FK_E477F63577B1A192 FOREIGN KEY (backup_type_id) REFERENCES backup_types (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE backup_jobs DROP FOREIGN KEY FK_3393E85977B1A192');
        $this->addSql('ALTER TABLE backup_parameters DROP FOREIGN KEY FK_E477F63577B1A192');
        $this->addSql('ALTER TABLE backup_job_parameters DROP FOREIGN KEY FK_767ED7424BC66710');
        $this->addSql('ALTER TABLE backup_job_parameters DROP FOREIGN KEY FK_767ED7427881A93C');
        $this->addSql('DROP TABLE backup_job_parameters');
        $this->addSql('DROP TABLE backup_types');
        $this->addSql('DROP TABLE backup_jobs');
        $this->addSql('DROP TABLE backup_parameters');
    }
}
