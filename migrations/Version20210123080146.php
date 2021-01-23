<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210123080146 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reset_password_request ADD CONSTRAINT FK_7CE748AA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "user" ADD email_verified_token VARCHAR(100)');
        $this->addSql('ALTER TABLE "user" ADD email_subscribed_token VARCHAR(100)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D64944DB2A83 ON "user" (email_verified_token) WHERE email_verified_token IS NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D6496EA0629F ON "user" (email_subscribed_token) WHERE email_subscribed_token IS NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE reset_password_request DROP CONSTRAINT FK_7CE748AA76ED395');
        $this->addSql('DROP INDEX UNIQ_8D93D64944DB2A83');
        $this->addSql('DROP INDEX UNIQ_8D93D6496EA0629F');
        $this->addSql('ALTER TABLE "user" DROP email_verified_token');
        $this->addSql('ALTER TABLE "user" DROP email_subscribed_token');
    }
}
