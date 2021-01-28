<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210128093024 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE "question_category_id_seq" INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE "question_category" (id INT NOT NULL, status VARCHAR(50) NOT NULL, title TEXT NOT NULL, slug VARCHAR(200) NOT NULL, href VARCHAR(250) NOT NULL, total_questions INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6544A9CD989D9B62 ON "question_category" (slug)');
        $this->addSql('CREATE INDEX question_category_status ON "question_category" (status)');
        $this->addSql('ALTER INDEX status RENAME TO user_status');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE "question_category_id_seq" CASCADE');
        $this->addSql('DROP TABLE "question_category"');
        $this->addSql('ALTER INDEX user_status RENAME TO status');
    }
}
