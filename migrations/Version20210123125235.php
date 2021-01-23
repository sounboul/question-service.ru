<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210123125235 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE reset_password_request_id_seq CASCADE');
        $this->addSql('DROP TABLE reset_password_request');
        $this->addSql('DROP INDEX UNIQ_8D93D64944DB2A83');
        $this->addSql('DROP INDEX UNIQ_8D93D6496EA0629F');
        $this->addSql('ALTER TABLE "user" ADD password_restore_token VARCHAR(100) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D6495AE23700 ON "user" (password_restore_token)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D64944DB2A83 ON "user" (email_verified_token)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D6496EA0629F ON "user" (email_subscribed_token)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE SEQUENCE reset_password_request_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE reset_password_request (id INT NOT NULL, user_id INT NOT NULL, selector VARCHAR(20) NOT NULL, hashed_token VARCHAR(100) NOT NULL, requested_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, expires_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_7ce748aa76ed395 ON reset_password_request (user_id)');
        $this->addSql('COMMENT ON COLUMN reset_password_request.requested_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN reset_password_request.expires_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE reset_password_request ADD CONSTRAINT fk_7ce748aa76ed395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('DROP INDEX UNIQ_8D93D6495AE23700');
        $this->addSql('DROP INDEX uniq_8d93d64944db2a83');
        $this->addSql('DROP INDEX uniq_8d93d6496ea0629f');
        $this->addSql('ALTER TABLE "user" DROP password_restore_token');
        $this->addSql('CREATE UNIQUE INDEX uniq_8d93d64944db2a83 ON "user" (email_verified_token) WHERE (email_verified_token IS NOT NULL)');
        $this->addSql('CREATE UNIQUE INDEX uniq_8d93d6496ea0629f ON "user" (email_subscribed_token) WHERE (email_subscribed_token IS NOT NULL)');
    }
}
