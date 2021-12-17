<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20211217153606 extends AbstractMigration
{
    private $lastInvoiceId;
    private $providerId;

    public function preUp(Schema $schema)
    {
        $enterprise = $this->connection->fetchAssoc('SELECT last_invoice_number FROM enterprise');
        if (!$enterprise) {
            throw new \RuntimeException('No enterprise was found.');
        }

        $providerAtrio = $this->connection->fetchAssoc('SELECT id FROM provider AS p WHERE p.name LIKE :s', ['s' => 'ATRIO']);
        if (!$providerAtrio) {
            throw new \RuntimeException('Provider "ATRIO" was found.');
        }

        $this->lastInvoiceId = $enterprise['last_invoice_number'];
        $this->providerId = $providerAtrio['id'];
    }

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE provider ADD last_invoice_auto_increment_value VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE enterprise DROP last_invoice_number');
    }

    public function postUp(Schema $schema)
    {
        $this->connection->update(
            'provider',
            ['last_invoice_auto_increment_value' => $this->lastInvoiceId],
            ['id' => $this->providerId]
        );
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE provider DROP last_invoice_auto_increment_value');
    }
}
