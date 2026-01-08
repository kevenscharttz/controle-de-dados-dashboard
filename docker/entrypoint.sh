#!/usr/bin/env sh
set -e

# Mensagem util no log
echo "[entrypoint] Iniciando container Laravel..."

# Garantir diretorios de cache/sessoes/views existentes e permissoes corretas
mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache || true
chown -R www-data:www-data storage bootstrap/cache || true
chmod -R 775 storage bootstrap/cache || true

# Gerar APP_KEY caso não exista
if [ -z "${APP_KEY}" ]; then
  echo "[entrypoint] Gerando APP_KEY..."
  php artisan key:generate --force >/dev/null 2>&1 || true
fi

# Se for SQLite, garanta o arquivo do banco
if [ "${DB_CONNECTION}" = "sqlite" ]; then
  echo "[entrypoint] DB_CONNECTION=sqlite: garantindo arquivo database/database.sqlite"
  mkdir -p database
  touch database/database.sqlite
fi

# Limpezas (nao falham o container se der erro)
php artisan config:clear >/dev/null 2>&1 || true
php artisan cache:clear  >/dev/null 2>&1 || true
php artisan route:clear  >/dev/null 2>&1 || true
php artisan view:clear   >/dev/null 2>&1 || true

# Link de storage (idempotente)
php artisan storage:link >/dev/null 2>&1 || true

# Rodar migracoes automaticamente; se falhar (ex: sem DB), apenas loga e continua
if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
  echo "[entrypoint] Executando migrations..."
  php artisan migrate --force || echo "[entrypoint] Migrations falharam (provavel DB indisponivel). Continuando..."
fi

# Seeders essenciais (idempotentes) - controlado por RUN_SEEDERS (padrao: true)
if [ "${RUN_SEEDERS:-true}" = "true" ]; then
  echo "[entrypoint] Executando seeders essenciais..."
  php artisan db:seed --class=PlatformRolesAndPermissionsSeeder --force || true
  php artisan db:seed --class=DockerSuperAdminSeeder --force || true
fi

# Otimizacoes
php artisan optimize || true

# Log quick assets sanity check (useful on Render logs)
echo "[entrypoint] Verificando assets compilados em public/build..."
if [ -f "public/build/manifest.json" ]; then
  head -n 50 public/build/manifest.json || true
else
  echo "[entrypoint] manifest.json NAO encontrado em public/build" || true
fi

ls -lah public/build/assets 2>/dev/null | head -n 50 || true
ls -lah public/build/assets/theme-*.css 2>/dev/null || true

# Iniciar servidor HTTP ouvindo na porta dinamica do Railway ($PORT)
PORT_TO_USE=${PORT:-8080}
echo "[entrypoint] Servidor ouvindo em 0.0.0.0:${PORT_TO_USE}"
exec php artisan serve --host=0.0.0.0 --port=${PORT_TO_USE}
