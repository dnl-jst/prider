<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170711070148 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE servers ADD group_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE servers ADD CONSTRAINT FK_4F8AF5F7FE54D947 FOREIGN KEY (group_id) REFERENCES groups (id)');
        $this->addSql('CREATE INDEX IDX_4F8AF5F7FE54D947 ON servers (group_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE servers DROP FOREIGN KEY FK_4F8AF5F7FE54D947');
        $this->addSql('DROP INDEX IDX_4F8AF5F7FE54D947 ON servers');
        $this->addSql('ALTER TABLE servers DROP group_id');
    }
}
