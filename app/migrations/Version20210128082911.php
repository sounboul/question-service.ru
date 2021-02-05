<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210128082911 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE "user" DROP CONSTRAINT FK_8D93D6497E9E4C8C');
        $this->addSql('ALTER TABLE "user" ADD CONSTRAINT FK_8D93D6497E9E4C8C FOREIGN KEY (photo_id) REFERENCES "user_photo" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE "user" DROP CONSTRAINT fk_8d93d6497e9e4c8c');
        $this->addSql('ALTER TABLE "user" ADD CONSTRAINT fk_8d93d6497e9e4c8c FOREIGN KEY (photo_id) REFERENCES user_photo (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
