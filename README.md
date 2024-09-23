# Решение первого задания

```SQL
with UserBooksWithWindow as (
SELECT
    u.id AS user_id, 
    CONCAT(u.first_name, ' ', u.last_name) AS Name, 
    b.author AS Author, 
    b.name AS Book,
    ub.return_date - ub.get_date AS days_held,
    COUNT(*) OVER (PARTITION BY u.id, b.author) AS author_book_count,
    DATE_PART('year', AGE(now(), u.birthday)) AS user_age
FROM 
    users u
JOIN 
    user_books ub ON u.id = ub.user_id
JOIN 
    books b ON ub.book_id = b.id
)

SELECT 
    user_id AS ID, 
    Name, 
    Author, 
    STRING_AGG(Book, ',') AS Books
FROM 
    UserBooksWithWindow
WHERE 
    user_age BETWEEN 7 AND 17  -- Возраст от 7 до 17 лет
    AND author_book_count = 2  -- Взяты ровно 2 книги одного автора
GROUP BY 
    user_id, Name, Author
having max(days_held) <= 14 -- Книги на руках не более 2 недель
;
```


# Задание 2

Задание реализовано с использование фреймворка Laravel 11.x

Для авторизации пользователя используется Laravel/Sanctum. Авторизация происходит через токен, который должен быть передан в заголовках запроса. Тип авторизации: `Authorization: Bearer`

Эндпоинты GET `/api/v1?method=rates` и POST `/api/v1?method=convert` защищены авторизацией.

Для авторизации пользователся токен можно получить через методы регистрации (`api/v1/register`) и авторизации (`api/v1/login`).

Перед началом развертывания необходимо создать и настроить файл окружения в `./application`.
Для этого необходимо скопировать содержимое файла `./application/.env.example` в файл `./application/.env` выполнив команду `cp ./application/.env.example ./application/.env`

В файле окружения нужно определить переменные для подключения к базе данных:

DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE={DATABASE}
DB_USERNAME={USERNAME}
DB_PASSWORD={PASSWORD}

После чего указать те же самые значения для пользователя, базы данных и пароля в файле `./infra/pgsql/script/init.sql` для создания пользователя и базы данных при загрузке контейнера.

После определения файла окружения и `./infra/pgsql/script/init.sql` развернуть контейнер через Docker.


```
docker-compose build app
docker-compose up -d
```

Nginx сервер будет прослушивать 80 порт для http и 443 порт для https запросов.
