#!/usr/bin/env bash
# Fresh demo database for one recording take; the Admin shows as "Head of Office".
set -e
cd /d/laravel-inertia/laravel-drs/backend
export DB_DATABASE="$(cygpath -w "$PWD/database/demo.sqlite")"
php artisan migrate:fresh --seed --force > /dev/null
php -r '$p=new PDO("sqlite:database/demo.sqlite"); $p->exec("update users set first_name=\"Head of\", last_name=\"Office\", position=null where email=\"admin@disasterresource.local\"");'
echo reset
