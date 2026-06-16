# Memória do Projeto: API Futebol v2

## Arquitetura e Decisões Técnicas

### 1. Generalização de Campeonatos (v2)
O sistema foi evoluído de uma estrutura fixa para o Brasileirão para uma arquitetura multifeed capaz de suportar qualquer liga mundial.
- **Tabelas Genéricas:** `championships`, `championship_editions`, `teams`, `matches` (via model `Game`) e `standings`.
- **Slugs:** Cada campeonato é identificado por um slug (ex: `premier-league`, `world-cup`).

### 2. Sistema de Scrapers
- **BaseScraper:** Classe abstrata para requisições HTTP via Guzzle.
- **ScraperInterface:** Contrato obrigatório para novos capturadores.
- **GenericEspnScraper:** Motor de captura robusto que processa o HTML da ESPN, usado por todos os campeonatos internacionais.
- **ScraperFactory:** Gerencia a instância correta do capturador baseado no slug.

### 3. Compatibilidade PHP 8.5+
- **Model Game:** O modelo de banco de dados para partidas foi nomeado como `Game.php` em vez de `Match.php` para evitar conflito com a palavra reservada `match` do PHP moderno.
- **Tabela matches:** Embora o Model se chame `Game`, a tabela no banco de dados permanece como `matches` por convenção semântica.

### 4. Integração Mobile
- A API agora fornece o endpoint `GET /api/v2/championships` para permitir que aplicativos móveis montem menus dinâmicos.

## Orientações para Expansão
Para adicionar um novo campeonato (ex: Liga Francesa):
1. Adicione o slug no `ChampionshipSeeder`.
2. Adicione a URL da ESPN no `.env`.
3. Registre o slug na `ScraperFactory` apontando para o `GenericEspnScraper` (ou crie um novo scraper se o site de origem for diferente).
