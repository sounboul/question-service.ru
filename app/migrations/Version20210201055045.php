<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210201055045 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE question ALTER category_id SET NOT NULL');
        $this->addSql('ALTER TABLE question ALTER status TYPE VARCHAR(20)');
        $this->addSql('ALTER TABLE question ALTER text DROP NOT NULL');
        $this->addSql('ALTER TABLE question ALTER href DROP NOT NULL');
        $this->addSql('ALTER TABLE question ALTER created_by_ip DROP NOT NULL');
        $this->addSql('ALTER TABLE question ALTER created_by_ip TYPE VARCHAR(46)');
        $this->addSql('ALTER TABLE question_answer ALTER question_id SET NOT NULL');
        $this->addSql('ALTER TABLE question_answer ALTER status TYPE VARCHAR(20)');
        $this->addSql('ALTER TABLE question_answer ALTER created_by_ip DROP NOT NULL');
        $this->addSql('ALTER TABLE question_answer ALTER created_by_ip TYPE VARCHAR(46)');
        $this->addSql('ALTER TABLE question_category ALTER status TYPE VARCHAR(20)');
        $this->addSql('ALTER TABLE question_category ALTER href DROP NOT NULL');
        $this->addSql('ALTER TABLE "user" ALTER status TYPE VARCHAR(20)');
        $this->addSql('ALTER TABLE "user" ALTER email TYPE VARCHAR(200)');
        $this->addSql('ALTER TABLE user_photo ALTER user_id SET NOT NULL');
        $this->addSql('ALTER TABLE user_photo ALTER status TYPE VARCHAR(20)');
        $this->addSql('ALTER TABLE user_photo ALTER thumbnail_path DROP NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE "user" ALTER status TYPE VARCHAR(50)');
        $this->addSql('ALTER TABLE "user" ALTER email TYPE VARCHAR(180)');
        $this->addSql('ALTER TABLE "user_photo" ALTER user_id DROP NOT NULL');
        $this->addSql('ALTER TABLE "user_photo" ALTER status TYPE VARCHAR(50)');
        $this->addSql('ALTER TABLE "user_photo" ALTER thumbnail_path SET NOT NULL');
        $this->addSql('ALTER TABLE "question_category" ALTER status TYPE VARCHAR(50)');
        $this->addSql('ALTER TABLE "question_category" ALTER href SET NOT NULL');
        $this->addSql('ALTER TABLE "question" ALTER category_id DROP NOT NULL');
        $this->addSql('ALTER TABLE "question" ALTER status TYPE VARCHAR(50)');
        $this->addSql('ALTER TABLE "question" ALTER text SET NOT NULL');
        $this->addSql('ALTER TABLE "question" ALTER href SET NOT NULL');
        $this->addSql('ALTER TABLE "question" ALTER created_by_ip SET NOT NULL');
        $this->addSql('ALTER TABLE "question" ALTER created_by_ip TYPE VARCHAR(250)');
        $this->addSql('ALTER TABLE "question_answer" ALTER question_id DROP NOT NULL');
        $this->addSql('ALTER TABLE "question_answer" ALTER status TYPE VARCHAR(50)');
        $this->addSql('ALTER TABLE "question_answer" ALTER created_by_ip SET NOT NULL');
        $this->addSql('ALTER TABLE "question_answer" ALTER created_by_ip TYPE VARCHAR(250)');
    }
}
