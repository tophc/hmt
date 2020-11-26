<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201125195000 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE administration (id INT AUTO_INCREMENT NOT NULL, nom_administration VARCHAR(255) NOT NULL, prenom_administration VARCHAR(255) NOT NULL, email_administration VARCHAR(255) NOT NULL, password_administration VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE affectation (id INT AUTO_INCREMENT NOT NULL, chauffeur_id INT NOT NULL, vehicule_id INT NOT NULL, tournee_id INT NOT NULL, date_affectation DATE NOT NULL, INDEX IDX_F4DD61D385C0B3BE (chauffeur_id), INDEX IDX_F4DD61D34A4A3511 (vehicule_id), INDEX IDX_F4DD61D3F661D013 (tournee_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE amende (id INT AUTO_INCREMENT NOT NULL, vehicule_id INT DEFAULT NULL, date_amende DATETIME NOT NULL, num_amende VARCHAR(255) NOT NULL, montant_amende DOUBLE PRECISION NOT NULL, remarque_amende LONGTEXT DEFAULT NULL, INDEX IDX_613014CF4A4A3511 (vehicule_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE categorie_permis_conduire (id INT AUTO_INCREMENT NOT NULL, nom_categorie_permis_conduire VARCHAR(255) NOT NULL, info_categorie_permis_conduire LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE categorie_permis_conduire_permis_conduire (categorie_permis_conduire_id INT NOT NULL, permis_conduire_id INT NOT NULL, INDEX IDX_4BAEA22EC7515256 (categorie_permis_conduire_id), INDEX IDX_4BAEA22EA0FB65D (permis_conduire_id), PRIMARY KEY(categorie_permis_conduire_id, permis_conduire_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE chauffeur (id INT AUTO_INCREMENT NOT NULL, etat_civil_chauffeur_id INT NOT NULL, nom_chauffeur VARCHAR(255) NOT NULL, prenom_chauffeur VARCHAR(255) NOT NULL, genre_chauffeur VARCHAR(255) NOT NULL, numero_national_chauffeur VARCHAR(255) NOT NULL, date_naissance_chauffeur DATE NOT NULL, adresse_postale_chauffeur VARCHAR(255) NOT NULL, mobile_chauffeur VARCHAR(255) DEFAULT NULL, email_chauffeur VARCHAR(255) NOT NULL, password_chauffeur VARCHAR(255) NOT NULL, statut_chauffeur TINYINT(1) DEFAULT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', INDEX IDX_5CA777B8E84CCFD5 (etat_civil_chauffeur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE code_postal (id INT AUTO_INCREMENT NOT NULL, tournee_id INT DEFAULT NULL, num_code_postal INT NOT NULL, localite_code_postal VARCHAR(255) NOT NULL, INDEX IDX_CC94AC37F661D013 (tournee_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE colis (id INT AUTO_INCREMENT NOT NULL, code_postal_id INT NOT NULL, numero_colis VARCHAR(255) NOT NULL, nom_destinataire VARCHAR(255) NOT NULL, prenom_destinataire VARCHAR(255) DEFAULT NULL, adresse_destinataire VARCHAR(255) NOT NULL, numero_adresse_destinataire VARCHAR(255) NOT NULL, note_colis LONGTEXT DEFAULT NULL, type_colis TINYINT(1) NOT NULL, express_colis TINYINT(1) NOT NULL, INDEX IDX_470BDFF9B2B59251 (code_postal_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE controle_technique (id INT AUTO_INCREMENT NOT NULL, vehicule_id INT NOT NULL, date_controle_technique DATETIME NOT NULL, statut_controle_technique TINYINT(1) NOT NULL, remarque_controle_technique LONGTEXT DEFAULT NULL, INDEX IDX_1DA6481D4A4A3511 (vehicule_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE entretien (id INT AUTO_INCREMENT NOT NULL, vehicule_id INT NOT NULL, date_entretien DATETIME NOT NULL, km_entretien INT NOT NULL, montant_entretien DOUBLE PRECISION NOT NULL, remarque_entretien LONGTEXT DEFAULT NULL, INDEX IDX_2B58D6DA4A4A3511 (vehicule_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE etat (id INT AUTO_INCREMENT NOT NULL, code_etat VARCHAR(255) NOT NULL, descriptif_etat LONGTEXT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE etat_civil (id INT AUTO_INCREMENT NOT NULL, nom_etat_civil VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE logistique (id INT AUTO_INCREMENT NOT NULL, nom_logistique VARCHAR(255) NOT NULL, prenom_logistique VARCHAR(255) NOT NULL, email_logistique VARCHAR(255) NOT NULL, password_logistique VARCHAR(255) NOT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE modele_vehicule (id INT AUTO_INCREMENT NOT NULL, nom_modele_vehicule VARCHAR(255) NOT NULL, marque_modele_vehicule VARCHAR(255) NOT NULL, capacite_modele_vehicule INT NOT NULL, intervalle_entretien_modele_vehicule INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE permis_conduire (id INT AUTO_INCREMENT NOT NULL, titulaire_permis_conduire_id INT NOT NULL, num_permis_conduire VARCHAR(255) NOT NULL, date_val_permis_conduire DATE NOT NULL, UNIQUE INDEX UNIQ_68488EF619367D83 (titulaire_permis_conduire_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE requete (id INT AUTO_INCREMENT NOT NULL, chauffeur_id INT DEFAULT NULL, secretariat_id INT DEFAULT NULL, logistique_id INT DEFAULT NULL, objet_requete VARCHAR(255) NOT NULL, message_requete LONGTEXT NOT NULL, date_requete DATETIME NOT NULL, statut_requete TINYINT(1) NOT NULL, date_statut_requete DATETIME DEFAULT NULL, service_requete VARCHAR(255) NOT NULL, requerant_requete VARCHAR(255) NOT NULL, fichier_url_requete VARCHAR(255) DEFAULT NULL, note_requete LONGTEXT DEFAULT NULL, INDEX IDX_1E6639AA85C0B3BE (chauffeur_id), INDEX IDX_1E6639AAA628C492 (secretariat_id), INDEX IDX_1E6639AAE48B9DB (logistique_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE secretariat (id INT AUTO_INCREMENT NOT NULL, nom_secretariat VARCHAR(255) NOT NULL, prenom_secretariat VARCHAR(255) NOT NULL, email_secretariat VARCHAR(255) NOT NULL, password_secretariat VARCHAR(255) NOT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE suivi_colis (id INT AUTO_INCREMENT NOT NULL, colis_id INT NOT NULL, etat_id INT NOT NULL, date_suivi_colis DATETIME NOT NULL, INDEX IDX_E407FFDA4D268D70 (colis_id), INDEX IDX_E407FFDAD5E86FF (etat_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tournee (id INT AUTO_INCREMENT NOT NULL, num_tournee VARCHAR(3) NOT NULL, info_tournee LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE vehicule (id INT AUTO_INCREMENT NOT NULL, modele_vehicule_id INT NOT NULL, immatriculation_vehicule VARCHAR(255) NOT NULL, num_chassis_vehicule VARCHAR(255) NOT NULL, statut_vehicule TINYINT(1) DEFAULT NULL, INDEX IDX_292FFF1D8003A08C (modele_vehicule_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE affectation ADD CONSTRAINT FK_F4DD61D385C0B3BE FOREIGN KEY (chauffeur_id) REFERENCES chauffeur (id)');
        $this->addSql('ALTER TABLE affectation ADD CONSTRAINT FK_F4DD61D34A4A3511 FOREIGN KEY (vehicule_id) REFERENCES vehicule (id)');
        $this->addSql('ALTER TABLE affectation ADD CONSTRAINT FK_F4DD61D3F661D013 FOREIGN KEY (tournee_id) REFERENCES tournee (id)');
        $this->addSql('ALTER TABLE amende ADD CONSTRAINT FK_613014CF4A4A3511 FOREIGN KEY (vehicule_id) REFERENCES vehicule (id)');
        $this->addSql('ALTER TABLE categorie_permis_conduire_permis_conduire ADD CONSTRAINT FK_4BAEA22EC7515256 FOREIGN KEY (categorie_permis_conduire_id) REFERENCES categorie_permis_conduire (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE categorie_permis_conduire_permis_conduire ADD CONSTRAINT FK_4BAEA22EA0FB65D FOREIGN KEY (permis_conduire_id) REFERENCES permis_conduire (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE chauffeur ADD CONSTRAINT FK_5CA777B8E84CCFD5 FOREIGN KEY (etat_civil_chauffeur_id) REFERENCES etat_civil (id)');
        $this->addSql('ALTER TABLE code_postal ADD CONSTRAINT FK_CC94AC37F661D013 FOREIGN KEY (tournee_id) REFERENCES tournee (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE colis ADD CONSTRAINT FK_470BDFF9B2B59251 FOREIGN KEY (code_postal_id) REFERENCES code_postal (id)');
        $this->addSql('ALTER TABLE controle_technique ADD CONSTRAINT FK_1DA6481D4A4A3511 FOREIGN KEY (vehicule_id) REFERENCES vehicule (id)');
        $this->addSql('ALTER TABLE entretien ADD CONSTRAINT FK_2B58D6DA4A4A3511 FOREIGN KEY (vehicule_id) REFERENCES vehicule (id)');
        $this->addSql('ALTER TABLE permis_conduire ADD CONSTRAINT FK_68488EF619367D83 FOREIGN KEY (titulaire_permis_conduire_id) REFERENCES chauffeur (id)');
        $this->addSql('ALTER TABLE requete ADD CONSTRAINT FK_1E6639AA85C0B3BE FOREIGN KEY (chauffeur_id) REFERENCES chauffeur (id)');
        $this->addSql('ALTER TABLE requete ADD CONSTRAINT FK_1E6639AAA628C492 FOREIGN KEY (secretariat_id) REFERENCES secretariat (id)');
        $this->addSql('ALTER TABLE requete ADD CONSTRAINT FK_1E6639AAE48B9DB FOREIGN KEY (logistique_id) REFERENCES logistique (id)');
        $this->addSql('ALTER TABLE suivi_colis ADD CONSTRAINT FK_E407FFDA4D268D70 FOREIGN KEY (colis_id) REFERENCES colis (id)');
        $this->addSql('ALTER TABLE suivi_colis ADD CONSTRAINT FK_E407FFDAD5E86FF FOREIGN KEY (etat_id) REFERENCES etat (id)');
        $this->addSql('ALTER TABLE vehicule ADD CONSTRAINT FK_292FFF1D8003A08C FOREIGN KEY (modele_vehicule_id) REFERENCES modele_vehicule (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE categorie_permis_conduire_permis_conduire DROP FOREIGN KEY FK_4BAEA22EC7515256');
        $this->addSql('ALTER TABLE affectation DROP FOREIGN KEY FK_F4DD61D385C0B3BE');
        $this->addSql('ALTER TABLE permis_conduire DROP FOREIGN KEY FK_68488EF619367D83');
        $this->addSql('ALTER TABLE requete DROP FOREIGN KEY FK_1E6639AA85C0B3BE');
        $this->addSql('ALTER TABLE colis DROP FOREIGN KEY FK_470BDFF9B2B59251');
        $this->addSql('ALTER TABLE suivi_colis DROP FOREIGN KEY FK_E407FFDA4D268D70');
        $this->addSql('ALTER TABLE suivi_colis DROP FOREIGN KEY FK_E407FFDAD5E86FF');
        $this->addSql('ALTER TABLE chauffeur DROP FOREIGN KEY FK_5CA777B8E84CCFD5');
        $this->addSql('ALTER TABLE requete DROP FOREIGN KEY FK_1E6639AAE48B9DB');
        $this->addSql('ALTER TABLE vehicule DROP FOREIGN KEY FK_292FFF1D8003A08C');
        $this->addSql('ALTER TABLE categorie_permis_conduire_permis_conduire DROP FOREIGN KEY FK_4BAEA22EA0FB65D');
        $this->addSql('ALTER TABLE requete DROP FOREIGN KEY FK_1E6639AAA628C492');
        $this->addSql('ALTER TABLE affectation DROP FOREIGN KEY FK_F4DD61D3F661D013');
        $this->addSql('ALTER TABLE code_postal DROP FOREIGN KEY FK_CC94AC37F661D013');
        $this->addSql('ALTER TABLE affectation DROP FOREIGN KEY FK_F4DD61D34A4A3511');
        $this->addSql('ALTER TABLE amende DROP FOREIGN KEY FK_613014CF4A4A3511');
        $this->addSql('ALTER TABLE controle_technique DROP FOREIGN KEY FK_1DA6481D4A4A3511');
        $this->addSql('ALTER TABLE entretien DROP FOREIGN KEY FK_2B58D6DA4A4A3511');
        $this->addSql('DROP TABLE administration');
        $this->addSql('DROP TABLE affectation');
        $this->addSql('DROP TABLE amende');
        $this->addSql('DROP TABLE categorie_permis_conduire');
        $this->addSql('DROP TABLE categorie_permis_conduire_permis_conduire');
        $this->addSql('DROP TABLE chauffeur');
        $this->addSql('DROP TABLE code_postal');
        $this->addSql('DROP TABLE colis');
        $this->addSql('DROP TABLE controle_technique');
        $this->addSql('DROP TABLE entretien');
        $this->addSql('DROP TABLE etat');
        $this->addSql('DROP TABLE etat_civil');
        $this->addSql('DROP TABLE logistique');
        $this->addSql('DROP TABLE modele_vehicule');
        $this->addSql('DROP TABLE permis_conduire');
        $this->addSql('DROP TABLE requete');
        $this->addSql('DROP TABLE secretariat');
        $this->addSql('DROP TABLE suivi_colis');
        $this->addSql('DROP TABLE tournee');
        $this->addSql('DROP TABLE vehicule');
    }
}
