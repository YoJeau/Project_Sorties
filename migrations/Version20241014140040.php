<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241014140040 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE city (cit_id INT AUTO_INCREMENT NOT NULL, cit_name VARCHAR(30) NOT NULL, cit_post_code VARCHAR(5) NOT NULL, PRIMARY KEY(cit_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE location (loc_id INT AUTO_INCREMENT NOT NULL, loc_city INT NOT NULL, loc_name VARCHAR(30) NOT NULL, loc_street VARCHAR(30) NOT NULL, loc_latitude DOUBLE PRECISION NOT NULL, loc_longitude DOUBLE PRECISION NOT NULL, INDEX IDX_5E9E89CBA7A4AE57 (loc_city), PRIMARY KEY(loc_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE participant (par_id INT AUTO_INCREMENT NOT NULL, par_site INT NOT NULL, par_username VARCHAR(180) NOT NULL, roles JSON NOT NULL, par_password VARCHAR(255) NOT NULL, par_last_name VARCHAR(30) NOT NULL, par_first_name VARCHAR(30) NOT NULL, par_phone VARCHAR(15) DEFAULT NULL, par_email VARCHAR(50) NOT NULL, par_picture VARCHAR(255) DEFAULT NULL, par_is_active VARCHAR(255) NOT NULL, INDEX IDX_D79F6B11501D2B80 (par_site), UNIQUE INDEX UNIQ_IDENTIFIER_PAR_USERNAME (par_username), PRIMARY KEY(par_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE site (sit_id INT AUTO_INCREMENT NOT NULL, sit_name VARCHAR(30) NOT NULL, PRIMARY KEY(sit_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE state (sta_id INT AUTO_INCREMENT NOT NULL, sta_label VARCHAR(30) NOT NULL, PRIMARY KEY(sta_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE subscribe (sub_id INT AUTO_INCREMENT NOT NULL, sub_participant_id INT NOT NULL, sub_trip_id INT NOT NULL, INDEX IDX_68B95F3EFCB8620D (sub_participant_id), INDEX IDX_68B95F3EDA6472FA (sub_trip_id), PRIMARY KEY(sub_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE trip (tri_id INT AUTO_INCREMENT NOT NULL, tri_state INT NOT NULL, tri_location INT NOT NULL, tri_organiser INT NOT NULL, tri_site INT NOT NULL, tri_name VARCHAR(30) NOT NULL, tri_starting_date DATE NOT NULL, tri_duration INT NOT NULL, tri_closing_date DATE NOT NULL, tri_max_inscription_number INT NOT NULL, tri_description LONGTEXT NOT NULL, tri_cancellation_reason LONGTEXT DEFAULT NULL, INDEX IDX_7656F53B23748668 (tri_state), INDEX IDX_7656F53B41726069 (tri_location), INDEX IDX_7656F53BAEC264D1 (tri_organiser), INDEX IDX_7656F53BEB705171 (tri_site), PRIMARY KEY(tri_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE location ADD CONSTRAINT FK_5E9E89CBA7A4AE57 FOREIGN KEY (loc_city) REFERENCES city (cit_id)');
        $this->addSql('ALTER TABLE participant ADD CONSTRAINT FK_D79F6B11501D2B80 FOREIGN KEY (par_site) REFERENCES site (sit_id)');
        $this->addSql('ALTER TABLE subscribe ADD CONSTRAINT FK_68B95F3EFCB8620D FOREIGN KEY (sub_participant_id) REFERENCES participant (par_id)');
        $this->addSql('ALTER TABLE subscribe ADD CONSTRAINT FK_68B95F3EDA6472FA FOREIGN KEY (sub_trip_id) REFERENCES trip (tri_id)');
        $this->addSql('ALTER TABLE trip ADD CONSTRAINT FK_7656F53B23748668 FOREIGN KEY (tri_state) REFERENCES state (sta_id)');
        $this->addSql('ALTER TABLE trip ADD CONSTRAINT FK_7656F53B41726069 FOREIGN KEY (tri_location) REFERENCES location (loc_id)');
        $this->addSql('ALTER TABLE trip ADD CONSTRAINT FK_7656F53BAEC264D1 FOREIGN KEY (tri_organiser) REFERENCES participant (par_id)');
        $this->addSql('ALTER TABLE trip ADD CONSTRAINT FK_7656F53BEB705171 FOREIGN KEY (tri_site) REFERENCES site (sit_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE location DROP FOREIGN KEY FK_5E9E89CBA7A4AE57');
        $this->addSql('ALTER TABLE participant DROP FOREIGN KEY FK_D79F6B11501D2B80');
        $this->addSql('ALTER TABLE subscribe DROP FOREIGN KEY FK_68B95F3EFCB8620D');
        $this->addSql('ALTER TABLE subscribe DROP FOREIGN KEY FK_68B95F3EDA6472FA');
        $this->addSql('ALTER TABLE trip DROP FOREIGN KEY FK_7656F53B23748668');
        $this->addSql('ALTER TABLE trip DROP FOREIGN KEY FK_7656F53B41726069');
        $this->addSql('ALTER TABLE trip DROP FOREIGN KEY FK_7656F53BAEC264D1');
        $this->addSql('ALTER TABLE trip DROP FOREIGN KEY FK_7656F53BEB705171');
        $this->addSql('DROP TABLE city');
        $this->addSql('DROP TABLE location');
        $this->addSql('DROP TABLE participant');
        $this->addSql('DROP TABLE site');
        $this->addSql('DROP TABLE state');
        $this->addSql('DROP TABLE subscribe');
        $this->addSql('DROP TABLE trip');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
