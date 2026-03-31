#!/bin/sh

# Script para iniciar el scheduler de Laravel en segundo plano

echo "Iniciando Laravel Scheduler..."

# Loop infinito que ejecuta el scheduler cada minuto
while true; do
    php /var/www/artisan schedule:run >> /var/log/scheduler.log 2>&1
    sleep 60
done
