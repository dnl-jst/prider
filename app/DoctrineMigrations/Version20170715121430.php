<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170715121430 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE servers ADD key_pair_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE servers ADD CONSTRAINT FK_4F8AF5F71C6E969D FOREIGN KEY (key_pair_id) REFERENCES key_pairs (id)');
        $this->addSql('CREATE INDEX IDX_4F8AF5F71C6E969D ON servers (key_pair_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE servers DROP FOREIGN KEY FK_4F8AF5F71C6E969D');
        $this->addSql('DROP INDEX IDX_4F8AF5F71C6E969D ON servers');
        $this->addSql('ALTER TABLE servers DROP key_pair_id');
    }
}
