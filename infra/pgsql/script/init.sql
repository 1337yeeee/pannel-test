CREATE USER laravel WITH PASSWORD 'secret';
CREATE DATABASE pannel_test OWNER laravel;
GRANT ALL PRIVILEGES ON DATABASE pannel_test TO laravel;
