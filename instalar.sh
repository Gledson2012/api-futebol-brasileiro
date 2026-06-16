#!/bin/bash

echo "⚽ Inicia a instalação da API de Futebol..."

# 1. Habilita drivers no PHP
echo "🔧 Habilita os drivers de banco de dados no PHP..."
sudo sed -i 's/;extension=pdo_mysql/extension=pdo_mysql/' /etc/php/php.ini
sudo sed -i 's/;extension=mysqli/extension=mysqli/' /etc/php/php.ini

# 2. Configura o Banco de Dados e o Utilizador
echo "🗄️ Configura o banco de dados e o utilizador 'api_futebol'..."
# Cria banco, utilizador e concede permissões
sudo mariadb -u root <<EOF
CREATE DATABASE IF NOT EXISTS api_futebol;
GRANT ALL PRIVILEGES ON api_futebol.* TO 'api_futebol'@'127.0.0.1' IDENTIFIED BY 'futebol123';
GRANT ALL PRIVILEGES ON api_futebol.* TO 'api_futebol'@'localhost' IDENTIFIED BY 'futebol123';
FLUSH PRIVILEGES;
EOF

# 3. Atualiza o .env com as novas credenciais
echo "📝 Atualiza o arquivo .env..."
sed -i 's/DB_HOST=localhost/DB_HOST=127.0.0.1/' .env
sed -i 's/DB_USERNAME=root/DB_USERNAME=api_futebol/' .env
sed -i 's/DB_USERNAME=postgres/DB_USERNAME=api_futebol/' .env
sed -i 's/DB_PASSWORD=/DB_PASSWORD=futebol123/' .env
sed -i 's/DB_PASSWORD=senha/DB_PASSWORD=futebol123/' .env

# 4. Limpa cache e executa migrações
echo "🚀 Gera a chave do app e cria as tabelas..."
php artisan key:generate --force
php artisan config:clear
php artisan migrate:fresh --force
php artisan db:seed --class=ChampionshipSeeder --force

echo ""
echo "✅ TUDO PRONTO! A API v2 foi instalada e configurada com sucesso."
echo "----------------------------------------------------------------"
echo "Para executar o servidor, utilize: php artisan serve"
echo ""
echo "Exemplos de acesso (v2):"
echo "Brasileirão: http://localhost:8000/api/v2/championships/brasileirao/standings/2026"
echo "Premier League: http://localhost:8000/api/v2/championships/premier-league/standings/2026"
echo "Champions League: http://localhost:8000/api/v2/championships/champions-league/standings/2026"
echo "Copa do Mundo: http://localhost:8000/api/v2/championships/world-cup/standings/2026"
echo "----------------------------------------------------------------"
