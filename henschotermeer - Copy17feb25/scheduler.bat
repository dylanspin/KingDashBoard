@echo off
cd C:\inetpub\wwwroot\parkingwarev2\parkingshop-central-server\
php artisan schedule:run 1>> NUL 2>&1