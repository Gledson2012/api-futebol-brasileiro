# API Futebol Brasileiro e Mundial ⚽

Este projeto disponibiliza uma *API* robusta para consulta de tabelas, rodadas e estatísticas de campeonatos nacionais e internacionais. Além do suporte ao Campeonato Brasileiro, o sistema conta com uma arquitetura moderna (v2) que abrange ligas globais como a *Premier League*, *Champions League* e a Copa do Mundo.

O sistema utiliza o *framework* [Laravel 8](https://laravel.com/docs/8.x) com [PHP 8](https://www.php.net/releases/8.0).

## 🚀 Novidades da Versão 2
- **Arquitetura Multicampeonatos:** Suporte nativo para qualquer liga do mundo via sistema de *Scrapers*.
- **Banco de Dados Normalizado:** Estrutura genérica para gerir temporadas, equipas e jogos.
- **Aplicativo Android Nativo:** Projeto base em *Kotlin* (*Jetpack Compose*) pronto para consumir a *API*.

## 🛠️ Instalação Rápida (Linux/Arch)

Para facilitar a configuração do ambiente (drivers *PHP*, Banco de Dados e tabelas), utilize o *script* automatizado:

```bash
chmod +x instalar.sh
./instalar.sh
```

### Instalação Manual

```bash
# Instala as dependências
$ composer install

# Configura o ambiente
$ cp .env.example .env
$ php artisan key:generate

# Cria as tabelas e popula os campeonatos mundiais
$ php artisan migrate
$ php artisan db:seed --class=ChampionshipSeeder
```

## 📡 API v2 (Endpoints Genéricos)

A nova versão da *API* utiliza *endpoints* dinâmicos baseados no *slug* do campeonato.

| Método | URL | Descrição |
| :--- | :--- | :--- |
| GET | `/api/v2/championships` | Lista todos os campeonatos disponíveis. |
| GET | `/api/v2/championships/{slug}/standings/{ano}` | Retorna a classificação de um campeonato específico. |
| POST | `/api/v2/championships/{slug}/update` | Força a atualização dos dados (*Scraping*). |

**Exemplos de *Slugs*:** `brasileirao`, `premier-league`, `champions-league`, `world-cup`, `la-liga`.

## 📱 Aplicativo Android

O projeto inclui o código base para um aplicativo *Android* desenvolvido em *Kotlin* com *Jetpack Compose*. 
O *app* conta com:
- **Tema Dinâmico:** Cores que se adaptam conforme o campeonato selecionado.
- **Navegação Fluida:** Entre a lista de campeonatos e a tabela de classificação.
- **Consumo de API:** Integração com *Retrofit* e carregamento de imagens com *Coil*.

## ⚙️ Configuração de Banco de Dados

Configure as credenciais conforme as variáveis do arquivo `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=api_futebol
DB_USERNAME=api_futebol
DB_PASSWORD=futebol123
```

---
*Este projeto é desenvolvido com foco em performance e escalabilidade para entusiastas de dados esportivos.*
