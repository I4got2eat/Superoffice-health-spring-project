# Health Spring - Employee Wellness Challenge

A Symfony-based monolithic web application for tracking employee wellness habits during a 3-month challenge (March 1 - May 31, 2026).

## Features

- **Simple Name-Based Authentication**: Users log in with just their name
- **Daily Habit Tracking**: Track 4 daily habits (Hydration, Sleep, Steps, Nutrition)
- **Weekly Habit Tracking**: Track 2 weekly habits (150 min Activity, 4 Social Interactions)
- **Dynamic Scoring**: Points calculated in real-time based on completed habits
- **3-Month Calendar View**: Visual calendar showing progress with color coding
- **Leaderboard**: Company-wide ranking system

## Requirements

- PHP 8.2 or higher
- Composer
- Node.js and npm (for Webpack Encore)
- SQLite (or MySQL/PostgreSQL)

## Installation

1. **Install PHP dependencies:**
   ```bash
   composer install
   ```

2. **Install Node dependencies:**
   ```bash
   npm install
   ```

3. **Configure database:**
   
   The project is configured to use SQLite by default. If you want to use MySQL or PostgreSQL, update the `DATABASE_URL` in `.env`:
   ```env
   DATABASE_URL="mysql://user:password@127.0.0.1:3306/health_spring?serverVersion=8.0"
   # or
   DATABASE_URL="postgresql://user:password@127.0.0.1:5432/health_spring?serverVersion=16"
   ```

4. **Run database migrations:**
   ```bash
   php bin/console doctrine:migrations:migrate
   ```

5. **Build assets:**
   ```bash
   npm run build
   # or for development with watch mode:
   npm run watch
   ```

6. **Start the Symfony server:**
   ```bash
   symfony server:start
   # or
   php -S localhost:8000 -t public
   ```

## Usage

1. Navigate to `http://localhost:8000`
2. Click "Join Challenge / Login"
3. Enter your name (if new, an account will be created automatically)
4. Start logging your daily and weekly habits!

## Project Structure

- `src/Entity/` - Doctrine entities (User, DailyLog, WeeklyLog)
- `src/Repository/` - Repository classes with score calculation methods
- `src/Controller/` - Controllers for routes
- `src/Security/` - Custom authentication system
- `src/Service/` - ChallengeService with hardcoded dates
- `templates/` - Twig templates
- `assets/` - Frontend assets (Bootstrap 5, SCSS)

## Challenge Dates

The challenge runs from **March 1, 2026** to **May 31, 2026**. These dates are hardcoded in `src/Service/ChallengeService.php`.

## Scoring System

- **Daily Habits**: 1 point each (max 4 points per day)
  - Hydration
  - Sleep
  - Steps
  - Nutrition

- **Weekly Habits**: 1 point each (max 2 points per week)
  - 150 minutes of physical activity
  - 4 social interactions

- **Total Score**: Sum of all daily points + all weekly points

## Routes

- `/` - Landing page with challenge information
- `/login` - Name-based login
- `/dashboard` - Personal dashboard with calendar and stats
- `/dashboard/log/{date}` - Log daily habits for a specific date
- `/leaderboard` - Company-wide leaderboard

## Development

To run in development mode:

```bash
# Terminal 1: Symfony server
symfony server:start

# Terminal 2: Webpack watch
npm run watch
```

## License

Proprietary - Internal use only
