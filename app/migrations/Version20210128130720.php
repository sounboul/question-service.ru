<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210128130720 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE "question_id_seq" INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE "question_answer_id_seq" INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE "question" (id INT NOT NULL, user_id INT DEFAULT NULL, category_id INT DEFAULT NULL, status VARCHAR(50) NOT NULL, title TEXT NOT NULL, text TEXT NOT NULL, slug VARCHAR(200) NOT NULL, href VARCHAR(250) NOT NULL, created_by_ip VARCHAR(250) NOT NULL, total_answers INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_B6F7494EA76ED395 ON "question" (user_id)');
        $this->addSql('CREATE INDEX IDX_B6F7494E12469DE2 ON "question" (category_id)');
        $this->addSql('CREATE INDEX question_status ON "question" (status)');
        $this->addSql('CREATE TABLE "question_answer" (id INT NOT NULL, user_id INT DEFAULT NULL, question_id INT DEFAULT NULL, status VARCHAR(50) NOT NULL, text TEXT NOT NULL, created_by_ip VARCHAR(250) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_DD80652DA76ED395 ON "question_answer" (user_id)');
        $this->addSql('CREATE INDEX IDX_DD80652D1E27F6BF ON "question_answer" (question_id)');
        $this->addSql('CREATE INDEX question_answer_status ON "question_answer" (status)');
        $this->addSql('ALTER TABLE "question" ADD CONSTRAINT FK_B6F7494EA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "question" ADD CONSTRAINT FK_B6F7494E12469DE2 FOREIGN KEY (category_id) REFERENCES "question_category" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "question_answer" ADD CONSTRAINT FK_DD80652DA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "question_answer" ADD CONSTRAINT FK_DD80652D1E27F6BF FOREIGN KEY (question_id) REFERENCES "question" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE "question_answer" DROP CONSTRAINT FK_DD80652D1E27F6BF');
        $this->addSql('DROP SEQUENCE "question_id_seq" CASCADE');
        $this->addSql('DROP SEQUENCE "question_answer_id_seq" CASCADE');
        $this->addSql('DROP TABLE "question"');
        $this->addSql('DROP TABLE "question_answer"');
    }
}
