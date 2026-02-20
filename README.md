# Health Spring - Employee Wellness Challenge

A Symfony-based monolithic web application for tracking employee wellness habits during a 3-month challenge (March 1 - May 31, 2026).

## Features

- **Simple Name-Based Authentication**: Users log in with just their name
- **Daily Habit Tracking**: Track 4 daily habits (Hydration, Sleep, Steps, Nutrition)
- **Weekly Habit Tracking**: Track 2 weekly habits (150 min Activity, 4 Social Interactions)
- **Dynamic Scoring**: Points calculated in real-time based on completed habits
- **3-Month Calendar View**: Visual calendar showing progress with color coding
- **Leaderboard**: Company-wide ranking system

## Development

To run in development mode:

```bash
# Terminal 1: Symfony server
symfony server:start

# Terminal 2: Webpack watch
npm run watch
```
