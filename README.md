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
git clone <https://github.com/Mathgrawe/emutua-test.git>
cd <emutua-test>
```

**2. Configurar o Arquivo de Ambiente (.env)**

Copie o arquivo de exemplo para criar sua configuração local.
```bash
cp .env.example .env
```
Abra o arquivo .env e garanta que as seguintes variáveis estejam configuradas corretamente:
```bash
# Configuração do Banco de Dados
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

# Variável para o Docker Build (Importante para Windows)
WWWGROUP=1000

# Drivers para contornar problemas de permissão em disco
LOG_CHANNEL=stderr
SESSION_DRIVER=array
CACHE_STORE=array
```

Nota: A variável WWWGROUP=1000 foi adicionada para resolver um problema comum de exit code: 3 durante o build da imagem Docker em ambientes Windows. Para contornar problemas de permissão persistentes, também foi necessário alterar os drivers LOG_CHANNEL, SESSION_DRIVER e CACHE_STORE para stderr e array. Essas mudanças garantem que o Laravel não precise de permissões de escrita em disco para funcionar.
**3. Subir os Containers Docker**

Este comando irá iniciar todos os serviços (Laravel, MySQL, OpenSearch). A primeira vez pode demorar para baixar as imagens.
```bash
docker compose up -d
```
**4. Instalar Dependências e Configurar a Aplicação**

Execute os comandos abaixo, um por um, para finalizar a configuração.
```bash
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
```
✅ Pronto! Seu ambiente de backend está configurado e rodando. A API está acessível em http://localhost.

**🚨 Troubleshooting de Permissões (Docker no Windows)**

Durante o desenvolvimento em ambiente Windows, foram encontrados problemas persistentes de permissão de escrita. Caso enfrente erros 500, o seguinte comando pode ser necessário para corrigir as permissões das pastas storage e bootstrap/cache:
```bash
docker compose exec -u root laravel.test sh -c "chown -R www-data:www-data storage bootstrap/cache && chmod -R 775 storage bootstrap/cache"
```
Após executar, limpe os caches do Laravel para aplicar as mudanças:
```bash
docker compose exec laravel.test php artisan optimize:clear
```
**🧪 Testando a API**

Use uma ferramenta como o Postman ou o curl para testar os endpoints.

Listar Todos os Produtos
Este comando deve retornar a lista com os 8 produtos criados pelo seeder.
```bash
docker compose exec laravel.test curl http://localhost/api/products
```
Buscar Produtos (via OpenSearch)
Busca por produtos que contenham o termo "Gamer".
```bash
docker compose exec laravel.test curl "http://localhost/api/products?search=Gamer"
```
Criar um Novo Produto (Exemplo)
Este comando cria um nono produto na base de dados.
No PowerShell, use aspas simples para proteger o '@'
```bash
docker compose exec laravel.test curl -i -X POST -H "Content-Type: application/json" -d '{"name":"Novo Headphone","description":"Qualidade de estúdio.","price":1250.00,"category":"Áudio"}' http://localhost/api/products
```
Deletar um Produto (Ex: ID 1)
```bash
docker compose exec laravel.test curl -i -X DELETE http://localhost/api/products/1
```
Testando a Integração OpenSearch (Isoladamente)
Para validar a lógica de indexação e busca sem passar pela camada web, você pode usar o comando Artisan customizado:
```bash
docker compose exec laravel.test php artisan test:opensearch
```
**🤔 Decisões de Arquitetura e Desafios**

Padrão Repository: A lógica de acesso a dados foi abstraída para promover código limpo e testável.

Injeção de Dependência: O Service Container do Laravel é usado para gerenciar as instâncias do EntityManager do Doctrine e do cliente do OpenSearch.

Validação com Form Requests: A validação é centralizada na classe StoreProductRequest para manter os controllers enxutos.

Migrations Híbridas: Devido a instabilidades dos pacotes da comunidade laravel-doctrine/migrations com o Laravel 11, foi tomada a decisão estratégica de utilizar as Migrations nativas do Laravel em conjunto com o ORM do Doctrine, cumprindo o requisito do teste da forma mais robusta possível.

Configuração de Ambiente: Durante o desenvolvimento, foram enfrentados e resolvidos diversos desafios complexos relacionados à configuração do ambiente Docker no Windows, incluindo incompatibilidade de pacotes, problemas de permissão de arquivo e configurações de CORS, demonstrando uma abordagem metódica de depuração.