# se2026

Symfony проект (локално, Windows + XAMPP).

## Изисквания
- PHP 8.2+
- Composer
- SQLite (използва се през Doctrine)

## Стартиране (локално)
1) Влез в папката на проекта:
   - `cd app`

2) Инсталирай зависимостите:
   - `composer install`

3) Провери настройката за база данни:
   - използва се `.env.local` (локално)

4) Пусни миграциите:
   - `php bin/console doctrine:migrations:migrate`

5) Стартирай проекта:
   - `php -S 127.0.0.1:8000 -t public`

## Тестов потребител
Ако имаш командата за създаване на потребител:
- `php bin/console app:user:create email@fmi.bg password123456`

## Полезни адреси
- Начало: `/`
- Вход: `/login`
- Профил: `/profile`
- Моите публикации: `/my-posts`
- Изход: `/logout`
