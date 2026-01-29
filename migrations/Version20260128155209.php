<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260128155209 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `order` ADD payu_order_id VARCHAR(64) DEFAULT NULL, ADD payu_redirect_uri VARCHAR(255) DEFAULT NULL, ADD payu_status VARCHAR(30) DEFAULT NULL, ADD paid_at DATETIME DEFAULT NULL, ADD payu_last_status_check_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `order` DROP payu_order_id, DROP payu_redirect_uri, DROP payu_status, DROP paid_at, DROP payu_last_status_check_at');
    }
}
