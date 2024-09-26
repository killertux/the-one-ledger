#!/bin/bash

cp .env.example .env
php artisan migrate:fresh
vendor/bin/phpunit
