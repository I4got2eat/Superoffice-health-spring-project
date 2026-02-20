<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260220092515 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create User, DailyLog, and WeeklyLog tables for Health Spring challenge';
    }

    public function up(Schema $schema): void
    {
        // Create user table
        $this->addSql('CREATE TABLE `user` (
            id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
            name VARCHAR(255) NOT NULL,
            CONSTRAINT unique_name UNIQUE (name)
        )');

        // Create daily_log table
        $this->addSql('CREATE TABLE daily_log (
            id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
            user_id INTEGER NOT NULL,
            date DATE NOT NULL,
            hydration_done BOOLEAN DEFAULT 0 NOT NULL,
            sleep_done BOOLEAN DEFAULT 0 NOT NULL,
            steps_done BOOLEAN DEFAULT 0 NOT NULL,
            nutrition_done BOOLEAN DEFAULT 0 NOT NULL,
            CONSTRAINT FK_DAILY_LOG_USER FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE,
            CONSTRAINT unique_user_date UNIQUE (user_id, date)
        )');

        // Create weekly_log table
        $this->addSql('CREATE TABLE weekly_log (
            id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
            user_id INTEGER NOT NULL,
            week_start_date DATE NOT NULL,
            activity_done BOOLEAN DEFAULT 0 NOT NULL,
            social_done BOOLEAN DEFAULT 0 NOT NULL,
            CONSTRAINT FK_WEEKLY_LOG_USER FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE,
            CONSTRAINT unique_user_week UNIQUE (user_id, week_start_date)
        )');

        // Create indexes
        $this->addSql('CREATE INDEX IDX_DAILY_LOG_USER ON daily_log (user_id)');
        $this->addSql('CREATE INDEX IDX_WEEKLY_LOG_USER ON weekly_log (user_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE weekly_log');
        $this->addSql('DROP TABLE daily_log');
        $this->addSql('DROP TABLE `user`');
    }
}
