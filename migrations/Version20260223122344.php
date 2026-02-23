<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260223122344 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__daily_log AS SELECT id, user_id, date, hydration_done, sleep_done, steps_done, nutrition_done FROM daily_log');
        $this->addSql('DROP TABLE daily_log');
        $this->addSql('CREATE TABLE daily_log (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, date DATE NOT NULL, hydration_done BOOLEAN DEFAULT 0 NOT NULL, sleep_done BOOLEAN DEFAULT 0 NOT NULL, steps_done BOOLEAN DEFAULT 0 NOT NULL, nutrition_done BOOLEAN DEFAULT 0 NOT NULL, CONSTRAINT FK_8D0D8EA9A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO daily_log (id, user_id, date, hydration_done, sleep_done, steps_done, nutrition_done) SELECT id, user_id, date, hydration_done, sleep_done, steps_done, nutrition_done FROM __temp__daily_log');
        $this->addSql('DROP TABLE __temp__daily_log');
        $this->addSql('CREATE UNIQUE INDEX unique_user_date ON daily_log (user_id, date)');
        $this->addSql('CREATE INDEX IDX_8D0D8EA9A76ED395 ON daily_log (user_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__user AS SELECT id, name FROM user');
        $this->addSql('DROP TABLE user');
        $this->addSql('CREATE TABLE user (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, work_email VARCHAR(255) DEFAULT NULL, login_password VARCHAR(20) DEFAULT NULL, is_admin BOOLEAN DEFAULT 0 NOT NULL)');
        $this->addSql('INSERT INTO user (id, name) SELECT id, name FROM __temp__user');
        $this->addSql('DROP TABLE __temp__user');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649CF69075B ON user (work_email)');
        $this->addSql('CREATE UNIQUE INDEX unique_name ON user (name)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__weekly_log AS SELECT id, user_id, week_start_date, activity_done, social_done FROM weekly_log');
        $this->addSql('DROP TABLE weekly_log');
        $this->addSql('CREATE TABLE weekly_log (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, week_start_date DATE NOT NULL, activity_done BOOLEAN DEFAULT 0 NOT NULL, social_done BOOLEAN DEFAULT 0 NOT NULL, CONSTRAINT FK_98B56700A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO weekly_log (id, user_id, week_start_date, activity_done, social_done) SELECT id, user_id, week_start_date, activity_done, social_done FROM __temp__weekly_log');
        $this->addSql('DROP TABLE __temp__weekly_log');
        $this->addSql('CREATE UNIQUE INDEX unique_user_week ON weekly_log (user_id, week_start_date)');
        $this->addSql('CREATE INDEX IDX_98B56700A76ED395 ON weekly_log (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__daily_log AS SELECT id, date, hydration_done, sleep_done, steps_done, nutrition_done, user_id FROM daily_log');
        $this->addSql('DROP TABLE daily_log');
        $this->addSql('CREATE TABLE daily_log (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, date DATE NOT NULL, hydration_done BOOLEAN DEFAULT 0 NOT NULL, sleep_done BOOLEAN DEFAULT 0 NOT NULL, steps_done BOOLEAN DEFAULT 0 NOT NULL, nutrition_done BOOLEAN DEFAULT 0 NOT NULL, user_id INTEGER NOT NULL, CONSTRAINT FK_DAILY_LOG_USER FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO daily_log (id, date, hydration_done, sleep_done, steps_done, nutrition_done, user_id) SELECT id, date, hydration_done, sleep_done, steps_done, nutrition_done, user_id FROM __temp__daily_log');
        $this->addSql('DROP TABLE __temp__daily_log');
        $this->addSql('CREATE INDEX IDX_DAILY_LOG_USER ON daily_log (user_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__user AS SELECT id, name FROM "user"');
        $this->addSql('DROP TABLE "user"');
        $this->addSql('CREATE TABLE "user" (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL)');
        $this->addSql('INSERT INTO "user" (id, name) SELECT id, name FROM __temp__user');
        $this->addSql('DROP TABLE __temp__user');
        $this->addSql('CREATE TEMPORARY TABLE __temp__weekly_log AS SELECT id, week_start_date, activity_done, social_done, user_id FROM weekly_log');
        $this->addSql('DROP TABLE weekly_log');
        $this->addSql('CREATE TABLE weekly_log (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, week_start_date DATE NOT NULL, activity_done BOOLEAN DEFAULT 0 NOT NULL, social_done BOOLEAN DEFAULT 0 NOT NULL, user_id INTEGER NOT NULL, CONSTRAINT FK_WEEKLY_LOG_USER FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO weekly_log (id, week_start_date, activity_done, social_done, user_id) SELECT id, week_start_date, activity_done, social_done, user_id FROM __temp__weekly_log');
        $this->addSql('DROP TABLE __temp__weekly_log');
        $this->addSql('CREATE INDEX IDX_WEEKLY_LOG_USER ON weekly_log (user_id)');
    }
}
