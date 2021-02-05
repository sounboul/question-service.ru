<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210125072933 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE "user_photo_id_seq" INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE "user_photo" (id INT NOT NULL, user_id INT DEFAULT NULL, status VARCHAR(50) NOT NULL, original_path TEXT NOT NULL, thumbnail_path TEXT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_F6757F40A76ED395 ON "user_photo" (user_id)');
        $this->addSql('CREATE INDEX user_photo_status ON "user_photo" (status)');
        $this->addSql('ALTER TABLE "user_photo" ADD CONSTRAINT FK_F6757F40A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "user" ADD photo_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD about TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD CONSTRAINT FK_8D93D6497E9E4C8C FOREIGN KEY (photo_id) REFERENCES "user_photo" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D6497E9E4C8C ON "user" (photo_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE "user" DROP CONSTRAINT FK_8D93D6497E9E4C8C');
        $this->addSql('DROP SEQUENCE "user_photo_id_seq" CASCADE');
        $this->addSql('DROP TABLE "user_photo"');
        $this->addSql('DROP INDEX UNIQ_8D93D6497E9E4C8C');
        $this->addSql('ALTER TABLE "user" DROP photo_id');
        $this->addSql('ALTER TABLE "user" DROP about');
    }
}
