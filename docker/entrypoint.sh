#!/usr/bin/env sh
set -e

# Mensagem util no log
echo "[entrypoint] Iniciando container Laravel..."

# Log de URLs base (ajuda a diagnosticar mixed-content)
echo "[entrypoint] APP_URL=${APP_URL:-unset} ASSET_URL=${ASSET_URL:-unset}"

# Se existir arquivo de hot reload do Vite (public/hot), remova para evitar que o app tente carregar assets via dev server.
rm -f public/hot 2>/dev/null || true

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

# Seeders essenciais (idempotentes)
# - RUN_SEEDERS controla seeding em geral (padrao: true)
# - RUN_ROLE_SEEDER controla o seeder de Roles/Permissions (padrao: false para NAO sobrescrever suas mudancas)
if [ "${RUN_SEEDERS:-true}" = "true" ]; then
  echo "[entrypoint] Executando seeders essenciais..."
  if [ "${RUN_ROLE_SEEDER:-false}" = "true" ]; then
    echo "[entrypoint] PlatformRolesAndPermissionsSeeder habilitado via RUN_ROLE_SEEDER=true"
    php artisan db:seed --class=PlatformRolesAndPermissionsSeeder --force || true
  else
    echo "[entrypoint] Pulando PlatformRolesAndPermissionsSeeder (RUN_ROLE_SEEDER=false)"
  fi
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
