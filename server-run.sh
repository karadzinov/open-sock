#!/usr/bin/env bash
php artisan server:run &
php artisan queue:work --queue=high,default &
