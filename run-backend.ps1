Write-Host "Running composer install..."
composer install --ignore-platform-reqs

Write-Host "Running doctrine:migrations:migrate..."
php bin/console doctrine:migrations:migrate

Write-Host "symfony server:start..."
symfony server:stop
symfony server:start --port=8080 --listen-ip=0.0.0.0