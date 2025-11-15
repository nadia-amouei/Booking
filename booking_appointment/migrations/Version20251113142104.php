<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251113142104 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE appointment (id INT AUTO_INCREMENT NOT NULL, customer_id_id INT NOT NULL, service_id_id INT NOT NULL, status VARCHAR(255) NOT NULL, start_at TIME NOT NULL, end_at TIME NOT NULL, INDEX IDX_FE38F844B171EB6C (customer_id_id), INDEX IDX_FE38F844D63673B0 (service_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE payment (id INT AUTO_INCREMENT NOT NULL, appointment_id_id INT DEFAULT NULL, customer_id_id INT DEFAULT NULL, status VARCHAR(255) NOT NULL, INDEX IDX_6D28840D9334AFB9 (appointment_id_id), INDEX IDX_6D28840DB171EB6C (customer_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE service (id INT AUTO_INCREMENT NOT NULL, provider_id_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, duration_minutes INT NOT NULL, status VARCHAR(255) NOT NULL, INDEX IDX_E19D9AD226122B23 (provider_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, role VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE appointment ADD CONSTRAINT FK_FE38F844B171EB6C FOREIGN KEY (customer_id_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE appointment ADD CONSTRAINT FK_FE38F844D63673B0 FOREIGN KEY (service_id_id) REFERENCES service (id)');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840D9334AFB9 FOREIGN KEY (appointment_id_id) REFERENCES appointment (id)');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840DB171EB6C FOREIGN KEY (customer_id_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE service ADD CONSTRAINT FK_E19D9AD226122B23 FOREIGN KEY (provider_id_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE appointment DROP FOREIGN KEY FK_FE38F844B171EB6C');
        $this->addSql('ALTER TABLE appointment DROP FOREIGN KEY FK_FE38F844D63673B0');
        $this->addSql('ALTER TABLE payment DROP FOREIGN KEY FK_6D28840D9334AFB9');
        $this->addSql('ALTER TABLE payment DROP FOREIGN KEY FK_6D28840DB171EB6C');
        $this->addSql('ALTER TABLE service DROP FOREIGN KEY FK_E19D9AD226122B23');
        $this->addSql('DROP TABLE appointment');
        $this->addSql('DROP TABLE payment');
        $this->addSql('DROP TABLE service');
        $this->addSql('DROP TABLE user');
    }
}
