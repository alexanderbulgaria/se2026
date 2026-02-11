# se2026

Symfony проект (локално, Windows + XAMPP).

## Изисквания
- PHP 8.2+
- Composer
- SQLite (използва се през Doctrine)

## Стартиране (локално)
1) Влизане в папката на проекта:
   - `cd app`

2) Инсталиране на зависимостите:
   - `composer install`

3) Проверка настройката за база данни:
   - използва се `.env.local` (локално)

4) Пускане на миграциите:
   - `php bin/console doctrine:migrations:migrate`

5) Стартиране на проекта:
   - `php -S 127.0.0.1:8000 -t public`

## Тестов потребител
Ссъздаване примерен потребител:
- `php bin/console app:user:create email@fmi.bg password123456`

## Полезни адреси
- Начало: `/`
- Вход: `/login`
- Профил: `/profile`
- Моите публикации: `/my-posts`
- Публикации (включително като съавтор): `/my-posts`
- Изход: `/logout`
