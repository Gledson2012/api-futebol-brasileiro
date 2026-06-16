#!/bin/bash

echo "⚽ Iniciando instalação da API de Futebol..."

# 1. Habilitar drivers no PHP
echo "🔧 Habilitando drivers de banco de dados no PHP..."
sudo sed -i 's/;extension=pdo_mysql/extension=pdo_mysql/' /etc/php/php.ini
sudo sed -i 's/;extension=mysqli/extension=mysqli/' /etc/php/php.ini

# 2. Configurar Banco de Dados e Usuário
echo "🗄️ Configurando banco de dados e usuário 'api_futebol'..."
# Criar banco, usuário e dar permissões (usando 127.0.0.1 para evitar problemas de socket)
sudo mariadb -u root <<EOF
CREATE DATABASE IF NOT EXISTS api_futebol;
GRANT ALL PRIVILEGES ON api_futebol.* TO 'api_futebol'@'127.0.0.1' IDENTIFIED BY 'futebol123';
GRANT ALL PRIVILEGES ON api_futebol.* TO 'api_futebol'@'localhost' IDENTIFIED BY 'futebol123';
FLUSH PRIVILEGES;
EOF

# 3. Atualizar o .env com as novas credenciais
echo "📝 Atualizando arquivo .env..."
sed -i 's/DB_HOST=localhost/DB_HOST=127.0.0.1/' .env
sed -i 's/DB_USERNAME=root/DB_USERNAME=api_futebol/' .env
sed -i 's/DB_USERNAME=postgres/DB_USERNAME=api_futebol/' .env
sed -i 's/DB_PASSWORD=/DB_PASSWORD=futebol123/' .env
sed -i 's/DB_PASSWORD=senha/DB_PASSWORD=futebol123/' .env

# 4. Limpar cache e rodar migrações
echo "🚀 Gerando chave do app e criando tabelas..."
php artisan key:generate --force
php artisan config:clear
php artisan migrate:fresh --force
php artisan db:seed --class=ChampionshipSeeder --force

echo ""
echo "✅ TUDO PRONTO! A API v2 foi instalada e configurada com sucesso."
echo "----------------------------------------------------------------"
echo "Para rodar o servidor, execute: php artisan serve"
echo "Depois acesse: http://localhost:8000/api/v2/championships/brasileirao/standings/2026"
echo "----------------------------------------------------------------"
