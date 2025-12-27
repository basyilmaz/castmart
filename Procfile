web: php artisan storage:link --force 2>/dev/null; php artisan route:clear; php artisan config:clear; php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
worker: php artisan queue:work --sleep=3 --tries=3 --max-time=3600
scheduler: php artisan schedule:work
