<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210531135224 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE conges (id INT AUTO_INCREMENT NOT NULL, users_id INT NOT NULL, date_demande DATE NOT NULL, date_depart DATE NOT NULL, date_retour DATE NOT NULL, motif VARCHAR(255) NOT NULL, etat VARCHAR(255) NOT NULL, INDEX IDX_6327DE3A67B3B43D (users_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE employe (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, adresse VARCHAR(255) NOT NULL, fonction VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE permission (id INT AUTO_INCREMENT NOT NULL, users_id INT NOT NULL, sujet VARCHAR(255) NOT NULL, state VARCHAR(255) NOT NULL, date_demande DATE NOT NULL, date_permission DATE NOT NULL, heure_depart TIME NOT NULL, heure_retour TIME NOT NULL, INDEX IDX_E04992AA67B3B43D (users_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE soldes (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, initial VARCHAR(255) NOT NULL, consomme VARCHAR(255) NOT NULL, restant VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_C8BEAA73A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, fonction VARCHAR(255) NOT NULL, adresse VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE conges ADD CONSTRAINT FK_6327DE3A67B3B43D FOREIGN KEY (users_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE permission ADD CONSTRAINT FK_E04992AA67B3B43D FOREIGN KEY (users_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE soldes ADD CONSTRAINT FK_C8BEAA73A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE conges DROP FOREIGN KEY FK_6327DE3A67B3B43D');
        $this->addSql('ALTER TABLE permission DROP FOREIGN KEY FK_E04992AA67B3B43D');
        $this->addSql('ALTER TABLE soldes DROP FOREIGN KEY FK_C8BEAA73A76ED395');
        $this->addSql('DROP TABLE conges');
        $this->addSql('DROP TABLE employe');
        $this->addSql('DROP TABLE permission');
        $this->addSql('DROP TABLE soldes');
        $this->addSql('DROP TABLE user');
    }
}
