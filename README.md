# Gerenciamento de Produtos - Teste Técnico eMutua Digital

Este projeto é uma aplicação web de gerenciamento de produtos desenvolvida como parte do processo seletivo para Desenvolvedor Full Stack na eMutua Digital. A aplicação consiste em um backend robusto em **PHP/Laravel** com **Doctrine ORM** e uma integração avançada com **OpenSearch**, além de um frontend reativo que será construído em **React/Next.js**.

## ✨ Principais Funcionalidades

* **Backend Completo:** API RESTful para um CRUD (`Create`, `Read`, `Update`, `Delete`) completo de produtos.
* **Arquitetura Sólida:** Utilização de padrões de projeto como **Repository Pattern** para desacoplar a lógica de negócios do acesso a dados e **Form Requests** para validações seguras e organizadas.
* **Doctrine ORM:** Integração com o Doctrine como ORM principal para o mapeamento de entidades, cumprindo o requisito central do teste.
* **Busca Avançada com OpenSearch:** Sincronização automática de dados do banco de dados principal (MySQL) com um índice no OpenSearch para permitir buscas textuais performáticas.
* **Ambiente Containerizado:** Aplicação 100% containerizada com **Docker** e Laravel Sail, garantindo um ambiente de desenvolvimento consistente e de fácil reprodução.

## 🛠️ Tech Stack

* **Backend:** PHP 8.3, Laravel 11
* **ORM:** Doctrine
* **Banco de Dados:** MySQL 8.0
* **Busca:** OpenSearch 2
* **Frontend (a ser desenvolvido):** React.js / Next.js com Tailwind CSS
* **Ambiente:** Docker / Laravel Sail

## 🚀 Instalação e Execução do Ambiente Local

Siga os passos abaixo para configurar e executar o projeto em seu ambiente local.

**Pré-requisitos:**
* Docker Desktop instalado e em execução.
* Git.

**1. Clonar o Repositório**
```bash
git clone <URL_DO_SEU_REPOSITORIO>
cd <NOME_DA_PASTA_DO_PROJETO>

2. Configurar o Ambiente
O projeto utiliza o Laravel Sail para gerenciar o ambiente Docker.

Criar o arquivo de ambiente (.env)
Copie o arquivo de exemplo para criar seu arquivo de configuração local.

Bash

cp .env.example .env
Garanta que o arquivo .env contenha as variáveis de banco de dados e OpenSearch que configuramos.

Subir os Containers Docker
Este comando irá baixar as imagens necessárias (pode demorar na primeira vez) e iniciar todos os serviços (Laravel, MySQL, OpenSearch).

Bash

# Se estiver usando Git Bash ou WSL no Windows
./vendor/bin/sail up -d

# Se estiver usando PowerShell no Windows
.\vendor\bin\sail up -d
3. Instalar as Dependências
Com os containers no ar, execute o Composer para instalar as dependências do PHP.

Bash

docker compose exec laravel.test composer install
4. Gerar a Chave da Aplicação

Bash

docker compose exec laravel.test php artisan key:generate
5. Executar as Migrations
Este comando irá criar a tabela products no banco de dados MySQL.

Bash

docker compose exec laravel.test php artisan migrate
6. Ajustar Permissões (Caso ocorram erros)
Em alguns ambientes (especialmente Docker no Windows), podem ocorrer problemas de permissão na pasta storage. Se você enfrentar erros 500, execute o seguinte comando:

Bash

docker compose exec -u root laravel.test chmod -R 777 storage bootstrap/cache
✅ Pronto! Seu ambiente de backend está configurado e rodando. A API está acessível em http://localhost.

🧪 Testando a API
Você pode usar uma ferramenta como o Postman, Insomnia ou o curl para testar os endpoints.

Listar Todos os Produtos
Bash

docker compose exec laravel.test curl http://localhost/api/products
Resultado esperado (inicialmente): []
Criar um Novo Produto
Crie um arquivo payload.json na raiz do projeto com o conteúdo:
JSON

{
    "name": "Laptop Gamer Nitro",
    "description": "Laptop com placa de vídeo dedicada para jogos.",
    "price": 7800.00,
    "category": "Eletrônicos"
}
Execute o comando POST:
PowerShell

# No PowerShell, use aspas simples para proteger o '@'
docker compose exec laravel.test curl -i -X POST -H "Content-Type: application/json" -d '@/var/www/html/payload.json' http://localhost/api/products
Resultado esperado: HTTP/1.1 201 Created e o JSON do produto criado.
Atualizar um Produto (Ex: ID 1)
Modifique o payload.json com os novos dados.
Execute o comando PUT:
PowerShell

# No PowerShell
docker compose exec laravel.test curl -i -X PUT -H "Content-Type: application/json" -d '@/var/www/html/payload.json' http://localhost/api/products/1
Resultado esperado: HTTP/1.1 200 OK e o JSON do produto atualizado.
Deletar um Produto (Ex: ID 1)
Bash

docker compose exec laravel.test curl -i -X DELETE http://localhost/api/products/1
Resultado esperado: HTTP/1.1 204 No Content
Testando a Busca com OpenSearch
Para validar a integração com o OpenSearch de forma isolada (sem depender da camada web), você pode usar o comando Artisan customizado que foi criado (TestOpenSearchCommand.php).

Bash

docker compose exec laravel.test php artisan test:opensearch
Resultado esperado: Mensagens de sucesso indicando que um documento foi criado, indexado e encontrado no OpenSearch.
🤔 Decisões de Arquitetura e Desafios
Durante o desenvolvimento, algumas decisões foram tomadas para garantir um código limpo, manutenível e robusto.

Padrão Repository: A lógica de acesso a dados foi abstraída em uma classe ProductRepository. Isso desacopla o Controller do ORM, facilitando testes e futuras manutenções. O Controller apenas orquestra o fluxo, enquanto o Repository lida com a persistência.

Injeção de Dependência: O EntityManager do Doctrine e o cliente do OpenSearch são injetados via construtor no Repository e no OpenSearchServiceProvider, aproveitando o Service Container do Laravel para gerenciar as instâncias de forma eficiente.

Validação com Form Requests: A validação dos dados de entrada é centralizada na classe StoreProductRequest, mantendo os métodos do Controller enxutos e focados em sua responsabilidade principal.

Desafios de Ambiente: Foi enfrentada uma série de desafios relacionados à configuração do ambiente Docker no Windows, incluindo:

Incompatibilidade de Pacotes: As bibliotecas da comunidade para integração do Doctrine (especialmente laravel-doctrine/migrations) se mostraram incompatíveis com o Laravel 11. A solução foi pivotar para uma abordagem híbrida e estável: utilizar as Migrations nativas do Laravel (robustas e confiáveis) em conjunto com o ORM do Doctrine (cumprindo o requisito do teste).
Problemas de Permissão: Ocorreram múltiplos erros de permissão de escrita na pasta storage devido à forma como o Docker/WSL2 gerencia os volumes mapeados do Windows. Após diversas tentativas de chown e chmod, a solução final que se provou mais estável nos testes foi isolar a pasta storage em um Volume Nomeado do Docker.
