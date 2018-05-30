<?php namespace Fisdap\Data\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Adding service accounts and permissions
 *
 * @author Ben Getsug <bgetsug@fisdap.net>
 */
class Version20160227023252 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE ServiceAccount (oauth2ClientId VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_B81BF5555E237E06 (name), PRIMARY KEY(oauth2ClientId)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ServiceAccountPermissions (serviceAccountClientId VARCHAR(255) NOT NULL, serviceAccountPermissionId INT NOT NULL, INDEX IDX_7F7B64BDF9D3407A (serviceAccountClientId), INDEX IDX_7F7B64BD870E9051 (serviceAccountPermissionId), PRIMARY KEY(serviceAccountClientId, serviceAccountPermissionId)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ServiceAccountPermission (id INT AUTO_INCREMENT NOT NULL, routeName VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_1E03761991F30BA8 (routeName), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE ServiceAccountPermissions ADD CONSTRAINT FK_7F7B64BDF9D3407A FOREIGN KEY (serviceAccountClientId) REFERENCES ServiceAccount (oauth2ClientId) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ServiceAccountPermissions ADD CONSTRAINT FK_7F7B64BD870E9051 FOREIGN KEY (serviceAccountPermissionId) REFERENCES ServiceAccountPermission (id) ON DELETE CASCADE');

        $this->addSql('INSERT INTO ServiceAccountPermission SET routeName = "commerce.orders.permissions.index"');
        $this->addSql('INSERT INTO ServiceAccountPermission SET routeName = "professions.index"');
        $this->addSql('INSERT INTO ServiceAccountPermission SET routeName = "programs.show"');
        $this->addSql('INSERT INTO ServiceAccountPermission SET routeName = "programs.store"');
        $this->addSql('INSERT INTO ServiceAccountPermission SET routeName = "programs.types.index"');
        $this->addSql('INSERT INTO ServiceAccountPermission SET routeName = "swagger.docs"');
        $this->addSql('INSERT INTO ServiceAccountPermission SET routeName = "swagger.ui"');
        $this->addSql('INSERT INTO ServiceAccountPermission SET routeName = "timezones.index"');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE ServiceAccountPermissions DROP FOREIGN KEY FK_7F7B64BDF9D3407A');
        $this->addSql('ALTER TABLE ServiceAccountPermissions DROP FOREIGN KEY FK_7F7B64BD870E9051');
        $this->addSql('DROP TABLE ServiceAccount');
        $this->addSql('DROP TABLE ServiceAccountPermissions');
        $this->addSql('DROP TABLE ServiceAccountPermission');
    }
}
