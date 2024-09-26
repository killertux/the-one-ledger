#!/bin/bash

cp .env.example .env
/var/www/app/vendor/bin/phpunit
