<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260215195856 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE opinion (id INT AUTO_INCREMENT NOT NULL, rating SMALLINT NOT NULL, comment LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, product_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_AB02B0274584665A (product_id), INDEX IDX_AB02B027A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE opinion ADD CONSTRAINT FK_AB02B0274584665A FOREIGN KEY (product_id) REFERENCES towar (TowId) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE opinion ADD CONSTRAINT FK_AB02B027A76ED395 FOREIGN KEY (user_id) REFERENCES shop_user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE opinion DROP FOREIGN KEY FK_AB02B0274584665A');
        $this->addSql('ALTER TABLE opinion DROP FOREIGN KEY FK_AB02B027A76ED395');
        $this->addSql('DROP TABLE opinion');
    }
}
