# NewUrl - Configuração do Ambiente Docker

## Pré-requisitos por Sistema Operacional

### macOS

1. **Docker Desktop para Mac**
   - Baixe o [Docker Desktop](https://www.docker.com/products/docker-desktop/) para macOS
   - Suporta processadores Intel e Apple Silicon (M1/M2)
   - Após instalar, abra o Docker Desktop e aguarde ele inicializar
   
2. **Otimizações para macOS**
   ```bash
   # No arquivo .env, adicione para melhor performance
   SAIL_FILES_POLLING=true  # Ajuda com problemas de file watching
   
   # Se estiver usando Apple Silicon (M1/M2), adicione
   DOCKER_DEFAULT_PLATFORM=linux/amd64
   ```

### Linux

1. **Docker Engine** - [Guia de Instalação Docker](https://docs.docker.com/engine/install/)
   ```bash
   # Ubuntu/Debian
   sudo apt-get update
   sudo apt-get install docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin

   # Fedora
   sudo dnf -y install docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin

   # Após instalar, adicione seu usuário ao grupo docker (evita usar sudo)
   sudo usermod -aG docker $USER
   newgrp docker
   ```

2. **Docker Compose** (já incluído no Docker Desktop para Windows/Mac)
   ```bash
   # Verificar a instalação
   docker compose version
   ```

3. **Git** (para clonar o repositório)
   ```bash
   # Ubuntu/Debian
   sudo apt-get install git

   # Fedora/RHEL
   sudo dnf install git
   ```

## Configuração do Projeto

1. **Clone o Repositório**
   ```bash
   git clone <URL_DO_REPOSITORIO>
   cd newUrl-main-main
   ```

2. **Copie o Arquivo de Ambiente**
   ```bash
   cp .env.example .env
   ```

3. **Configure o .env** (valores importantes)
   ```ini
   APP_NAME=Laravel
   APP_URL=http://localhost

   DB_CONNECTION=mysql
   DB_HOST=mysql
   DB_PORT=3306
   DB_DATABASE=laravel
   DB_USERNAME=sail
   DB_PASSWORD=password

   SESSION_DRIVER=database
   ```

## Comandos Essenciais

1. **Primeira Execução**
   ```bash
   # Instalar dependências do Laravel Sail
   docker run --rm \
       -u "$(id -u):$(id -g)" \
       -v "$(pwd):/var/www/html" \
       -w /var/www/html \
       laravelsail/php82-composer:latest \
       composer install --ignore-platform-reqs

   # Iniciar os containers
   ./vendor/bin/sail up -d

   # Gerar chave da aplicação
   ./vendor/bin/sail artisan key:generate

   # Rodar migrações (IMPORTANTE: cria tabela de sessions)
   ./vendor/bin/sail artisan migrate

   # Criar usuário admin
   ./vendor/bin/sail artisan shield:super-admin
   ```

   > Após executar o comando `shield:super-admin`, siga as instruções no terminal para criar o usuário administrador. Você precisará fornecer:
   > - Nome
   > - Email
   > - Senha

2. **Comandos Diários**
   ```bash
   # Iniciar containers
   ./vendor/bin/sail up -d

   # Parar containers
   ./vendor/bin/sail down

   # Ver logs
   ./vendor/bin/sail logs

   # Acessar terminal do container
   ./vendor/bin/sail shell

   # Verificar status das migrações
   ./vendor/bin/sail artisan migrate:status

   # Executar migrações pendentes
   ./vendor/bin/sail artisan migrate
   ```

## Comandos de Desenvolvimento
   ```bash
   # Instalar dependências NPM
   ./vendor/bin/sail npm install

   # Compilar assets
   ./vendor/bin/sail npm run build

   # Rodar em modo desenvolvimento
   ./vendor/bin/sail npm run dev
   ```

## Criação de Usuário Admin

Existem duas maneiras de criar um usuário administrador:

### 1. Usando o Seeder (Recomendado)

1. Configure as variáveis no `.env`:
   ```ini
   ADMIN_NAME="Admin User"
   ADMIN_EMAIL=admin@example.com
   ADMIN_PASSWORD=sua_senha_segura
   # OU usando as variáveis alternativas
   DOCKER_ADMIN_EMAIL=admin@example.com
   DOCKER_ADMIN_PASSWORD=sua_senha_segura
   ```

2. Execute os seeders:
   ```bash
   # Roda todas as seeds (recomendado)
   ./vendor/bin/sail artisan db:seed

   # OU execute os seeders individualmente na seguinte ordem:
   ./vendor/bin/sail artisan db:seed --class=PlatformRolesAndPermissionsSeeder
   ./vendor/bin/sail artisan db:seed --class=DockerSuperAdminSeeder
   ./vendor/bin/sail artisan db:seed --class=OrganizationDataSeeder  # (opcional) Dados de exemplo
   ./vendor/bin/sail artisan db:seed --class=AddLogosToOrganizationsSeeder  # (opcional) Logos de exemplo
   ```

   > **Nota sobre os Seeders:**
   > - `PlatformRolesAndPermissionsSeeder`: Configura as permissões e papéis básicos do sistema
   > - `DockerSuperAdminSeeder`: Cria o usuário admin com as credenciais definidas no .env
   > - `OrganizationDataSeeder`: (Opcional) Adiciona organizações de exemplo
   > - `AddLogosToOrganizationsSeeder`: (Opcional) Adiciona logos de exemplo às organizações
   > - `TestDataSeeder`: Apenas para ambiente de desenvolvimento/testes
   ```

### 2. Criação Manual via Tinker

Se precisar criar um admin manualmente:

```bash
# Acesse o Tinker
./vendor/bin/sail artisan tinker

# No Tinker, execute:
$user = new App\Models\User();
$user->name = 'Admin';
$user->email = 'admin@exemplo.com';
$user->password = Hash::make('sua_senha_segura');
$user->save();

# Atribua a role de super-admin
$user->assignRole('super-admin');
```

### Verificação

Para verificar se o usuário admin foi criado:

```bash
# Lista todos os usuários
./vendor/bin/sail artisan tinker --execute="App\Models\User::all()"

# OU verifique as roles
./vendor/bin/sail artisan tinker --execute="App\Models\User::with('roles')->get()"
```

## Acessando a Aplicação

Após iniciar os containers com `sail up -d`:
1. Frontend: [http://localhost](http://localhost)
2. Banco de dados: acessível via `localhost:3306` (credenciais no .env)

## Solução de Problemas
### Resetando o Banco de Dados MySQL (Problemas de Socket/Volume)

Se o container do MySQL não iniciar corretamente em uma nova máquina, ou aparecer erro de socket travado ("Another process with pid ... is using unix socket file"), siga estes passos para resetar o volume do banco:

```bash
# Pare todos os containers
./vendor/bin/sail down

# Remova o volume do MySQL (isso apaga todos os dados do banco!)
docker volume rm newurl-main-main-main_sail-mysql

# Suba novamente os containers
./vendor/bin/sail up -d
```

> **Dica:** Se for ambiente de desenvolvimento, pode remover o volume sem problemas. Se precisar manter os dados, faça backup antes.


### Problemas Comuns (Todos os Sistemas)

1. **Erro: "Table 'laravel.sessions' doesn't exist"**
   ```bash
   ./vendor/bin/sail artisan migrate
   ```

2. **Erro: "Connection refused" no banco de dados**
   - Verifique se o container do MySQL está rodando:
   ```bash
   ./vendor/bin/sail ps
   ```
   - Aguarde alguns segundos após iniciar os containers (MySQL precisa inicializar)

### Problemas Específicos do macOS

1. **Performance lenta de arquivos**
   - Adicione ao `.env`:
   ```ini
   SAIL_FILES_POLLING=true
   ```
   - Use o cache do Vite:
   ```bash
   ./vendor/bin/sail npm run build
   ```

2. **Problemas com Apple Silicon (M1/M2)**
   - Se encontrar erros de compatibilidade:
   ```ini
   # Adicione ao .env
   DOCKER_DEFAULT_PLATFORM=linux/amd64
   ```
   - Reconstrua os containers:
   ```bash
   ./vendor/bin/sail build --no-cache
   ```

3. **Docker Desktop consumindo muita memória**
   - Abra Docker Desktop → Settings → Resources
   - Ajuste Memory para 4GB
   - Ajuste Swap para 1GB
   - Clique em Apply & Restart

### Problemas Específicos do Linux

1. **Erro de permissão nos arquivos**
   ```bash
   # Ajustar permissões (execute fora do container)
   sudo chown -R $USER: .
   ```

4. **Limpar cache após mudanças**
   ```bash
   ./vendor/bin/sail artisan config:clear
   ./vendor/bin/sail artisan cache:clear
   ```

## Comandos Docker Úteis

```bash
# Ver containers rodando
docker ps

# Ver logs de um container específico
docker logs -f newurl-main-main-main-mysql-1

# Remover todos containers e volumes (reset completo)
docker compose down -v

# Reconstruir containers (após mudanças no Dockerfile)
./vendor/bin/sail build --no-cache
```

## Notas Importantes

1. A primeira execução pode demorar alguns minutos enquanto as imagens são baixadas
2. Sempre execute `sail artisan migrate` após clonar ou atualizar o projeto
3. O driver de sessão está configurado para database, então as migrations são necessárias
4. Use `sail artisan migrate:status` para verificar o estado das migrações

### Notas Específicas para macOS

1. **Performance e Sistema de Arquivos**
   - O Docker no macOS usa um sistema de arquivos virtualizado
   - Use `SAIL_FILES_POLLING=true` para melhor detecção de alterações
   - O NPM pode ser mais lento que no Linux; use `npm run build` em vez de `npm run dev`

2. **Recursos e Memória**
   - O Docker Desktop no Mac pode usar bastante memória
   - Configure limites de memória nas preferências do Docker Desktop
   - Feche o Docker Desktop quando não estiver usando para liberar recursos

3. **Compatibilidade M1/M2**
   - A maioria das imagens já tem suporte nativo para Apple Silicon
   - Use `--platform=linux/amd64` apenas se encontrar problemas específicos
   - Mantenha o Docker Desktop atualizado para melhor compatibilidade

## Estrutura Docker

O projeto usa Laravel Sail, que fornece:
- PHP 8.2 + Composer
- MySQL 8.0
- Node.js para assets
- Redis (opcional)

## Upload de Imagens / Logos

Se ao enviar uma imagem ela não carregar no painel, verifique:

1. Se existe o symlink `public/storage`. Dentro da pasta `public` deve aparecer uma pasta `storage`. Caso não exista:
   ```bash
   ./vendor/bin/sail artisan storage:link
   ```
2. A variável `APP_URL` no `.env` deve apontar para a URL acessível do container. Em ambiente local normalmente:
   ```ini
   APP_URL=http://localhost
   ```
3. Permissões da pasta `storage/app/public` (no host) precisam permitir escrita pelo usuário do container (WWWUSER). Você pode ajustar:
   ```bash
   sudo chown -R $USER:$USER storage/ bootstrap/cache
   ```
4. Após ajustes limpe caches:
   ```bash
   ./vendor/bin/sail artisan config:clear
   ./vendor/bin/sail artisan cache:clear
   ```

### Caminho dos Arquivos
O componente de upload salva os logos em: `storage/app/public/organizations/logos/`. A URL pública gerada será: `APP_URL/storage/organizations/logos/<arquivo>`.

### Criação Automática do Symlink
Para facilitar o desenvolvimento, o `AppServiceProvider` cria automaticamente o link quando em ambiente `local` se ele não existir. Em produção recomenda-se executar manualmente:
```bash
php artisan storage:link
``` 

### Dicas Extras
- Se precisar manter o nome original, já existe o campo `logo_filename` salvo; você pode exibir esse nome em telas futuras.
- Para prevenir conflitos de cache de imagens após substituição, considere adicionar query string de versão ao exibir (`?v=<updated_at_timestamp>`).
- Para suporte a CDN/S3 basta configurar o disk `s3` e trocar `->disk('public')` para `->disk('s3')` no formulário.

