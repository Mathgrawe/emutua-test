# Gerenciamento de Produtos - API RESTful

Este projeto é uma API RESTful completa para gerenciamento de produtos, desenvolvida como parte do processo seletivo para Desenvolvedor Full Stack na eMutua Digital.

A API foi construída com **Laravel 11** e segue princípios de arquitetura de software limpa, utilizando **Doctrine ORM** para mapeamento de objetos, **Repository Pattern** para abstração da camada de dados, e uma integração com **OpenSearch** para buscas avançadas.

## ✨ Principais Funcionalidades

* **API RESTful Completa:** Endpoints para CRUD (`Create`, `Read`, `Update`, `Delete`) de produtos.
* **Arquitetura Sólida:** Código desacoplado e organizado utilizando:
    * **Repository Pattern:** A lógica de acesso a dados (Doctrine/MySQL e OpenSearch) é isolada em uma classe `ProductRepository`.
    * **Injeção de Dependência:** O Service Container do Laravel é usado para gerenciar e injetar dependências como o `EntityManager` do Doctrine e o cliente do `OpenSearch`.
    * **Form Requests:** A validação das requisições de criação e atualização é centralizada na classe `StoreProductRequest` para manter os controllers limpos e seguros.
* **Doctrine ORM:** Utilização do Doctrine como ORM principal para o mapeamento de entidades, cumprindo o requisito central do teste.
* **Busca Avançada com OpenSearch:** Sincronização automática de dados com um índice no OpenSearch a cada criação, atualização ou exclusão de produto, permitindo buscas textuais performáticas.
* **Ambiente 100% Containerizado:** A aplicação roda em um ambiente Docker totalmente configurado, garantindo consistência e facilidade de reprodução.

## 🛠️ Tech Stack

* **Backend:** PHP 8.3, Laravel 11
* **ORM:** Doctrine
* **Banco de Dados:** MySQL 8.0
* **Busca:** OpenSearch 2
* **Ambiente:** Docker

---

## 🚀 Instalação e Execução do Ambiente Local

Siga os passos abaixo para configurar e executar o projeto.

**Pré-requisitos:**
* Docker Desktop instalado e em execução.
* Git.

**1. Clonar o Repositório**
```bash
git clone <URL_DO_SEU_REPOSITORIO>
cd <NOME_DA_PASTA_DO_PROJETO>

2. Configurar o Arquivo de Ambiente (.env)
Copie o arquivo de exemplo para criar sua configuração local.

cp .env.example .env

Abra o arquivo .env e garanta que as seguintes variáveis estejam configuradas corretamente:

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel # Ou o nome que preferir
DB_USERNAME=sail
DB_PASSWORD=password

# Configurações do OpenSearch
OPENSEARCH_HOST=opensearch
OPENSEARCH_PORT=9200
OPENSEARCH_SCHEME=http

3. Subir os Containers Docker
Este comando irá iniciar todos os serviços (Laravel, MySQL, OpenSearch). A primeira vez pode demorar alguns minutos para baixar as imagens.

docker compose up -d

4. Instalar Dependências e Configurar a Aplicação
Execute os comandos abaixo, um por um, para finalizar a configuração.

# Instalar dependências do PHP com o Composer
docker compose exec laravel.test composer install

# Gerar a chave da aplicação
docker compose exec laravel.test php artisan key:generate

# Publicar o arquivo de configuração do CORS
docker compose exec laravel.test php artisan config:publish cors

# Criar a estrutura da tabela 'products' no banco de dados
docker compose exec laravel.test php artisan migrate

# (Opcional) Popular o banco de dados com 8 produtos de exemplo
docker compose exec laravel.test php artisan db:seed

✅ A API está acessível em http://localhost.

🚨 Troubleshooting de Permissões (Docker no Windows)
Durante o desenvolvimento, foram encontrados problemas persistentes de permissão de escrita nas pastas storage e bootstrap/cache devido à forma como o Docker Desktop no Windows gerencia os volumes. Caso enfrente erros 500, os seguintes comandos, executados dentro do container, podem resolver o problema:

# 1. Criar a estrutura de diretórios esperada pelo Laravel
mkdir -p storage/framework/views storage/framework/cache storage/framework/sessions storage/framework/testing

# 2. Mudar o dono das pastas para o usuário do servidor web
chown -R www-data:www-data storage bootstrap/cache

# 3. Dar as permissões corretas de escrita
chmod -R 775 storage bootstrap/cache

Para executar estes comandos de uma só vez:

docker compose exec -u root laravel.test sh -c "mkdir -p storage/framework/views storage/framework/cache && chown -R www-data:www-data storage bootstrap/cache && chmod -R 775 storage bootstrap/cache"

Após executar, limpe os caches do Laravel para aplicar as mudanças:

docker compose exec laravel.test php artisan optimize:clear

🧪 Testando a API
Use uma ferramenta como o Postman ou o curl para testar os endpoints.

Listar Todos os Produtos
Bash

docker compose exec laravel.test curl http://localhost/api/products
Buscar Produtos (via OpenSearch)
Bash

docker compose exec laravel.test curl "http://localhost/api/products?search=termo_de_busca"
Criar um Novo Produto
Crie um arquivo payload.json com os dados do produto.
Execute o comando POST:
Bash

# No PowerShell, use aspas simples para proteger o '@'
docker compose exec laravel.test curl -i -X POST -H "Content-Type: application/json" -d '@/var/www/html/payload.json' http://localhost/api/products
Deletar um Produto (Ex: ID 1)
Bash

docker compose exec laravel.test curl -i -X DELETE http://localhost/api/products/1
🤔 Decisões de Arquitetura e Desafios
Padrão Repository: A lógica de acesso a dados foi abstraída para promover código limpo e testável.
Injeção de Dependência: O Service Container do Laravel é usado para gerenciar as instâncias do EntityManager do Doctrine e do cliente do OpenSearch.
Validação com Form Requests: A validação é centralizada na classe StoreProductRequest para manter os controllers enxutos.
Migrations Híbridas: Devido a instabilidades dos pacotes da comunidade laravel-doctrine/migrations com o Laravel 11, foi tomada a decisão estratégica de utilizar as Migrations nativas do Laravel em conjunto com o ORM do Doctrine, cumprindo o requisito do teste da forma mais robusta possível.