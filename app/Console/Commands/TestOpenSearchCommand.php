<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Repositories\ProductRepository;
use App\Entities\Product;
use ReflectionClass;

class TestOpenSearchCommand extends Command
{
    /**
     * comando no console.
     */
    protected $signature = 'test:opensearch';

    /**
     * Descrição do comando.
     */
    protected $description = 'Testa a indexação e a busca de um documento no OpenSearch.';

    /**
     * Injeta ProductRepository para que possamos usá-lo.
     */
    public function __construct(private ProductRepository $repository)
    {
        parent::__construct();
    }

    /**
     * lógica principal do nosso teste.
     */
    public function handle()
    {
        $this->info('Iniciando teste do OpenSearch...');

        // 1. Criar um produto FALSO, apenas em memória.
        $this->info('1. Criando um produto em memória...');
        $product = new Product();

        $reflectionClass = new ReflectionClass($product);
        $reflectionProperty = $reflectionClass->getProperty('id');
        $reflectionProperty->setAccessible(true);
        $testId = rand(1000, 9999);
        $reflectionProperty->setValue($product, $testId);

        $product->setName('Teste de Busca Avançada');
        $product->setDescription('Este é um item para testar a busca no OpenSearch.');
        $product->setPrice(123.45);
        $product->setCategory('Testes de Integração');
        $this->comment("-> Produto criado em memória com ID: {$testId}");

        // 2. Tenta indexar o produto no OpenSearch.
        try {
            $this->info('2. Indexando o produto no OpenSearch...');
            
            $repoReflection = new ReflectionClass($this->repository);
            $indexMethod = $repoReflection->getMethod('index');
            $indexMethod->setAccessible(true);
            $indexMethod->invoke($this->repository, $product);

            $this->info('-> Produto indexado com sucesso!');
        } catch (\Exception $e) {
            $this->error('### FALHA AO INDEXAR ###');
            $this->error($e->getMessage());
            return 1;
        }
        
        $this->info('-> Aguardando 2 segundos para o índice atualizar...');
        sleep(2);

        // 3. Tenta buscar pelo produto no OpenSearch.
        try {
            $this->info('3. Buscando pelo termo "Avançada" no OpenSearch...');
            $results = $this->repository->searchOnOpenSearch('Avançada');

            if (empty($results)) {
                $this->error('### FALHA NA BUSCA: Nenhum resultado encontrado. ###');
                return 1;
            }

            $this->info('-> Busca retornou resultados!');
            $this->comment(json_encode($results, JSON_PRETTY_PRINT));
            $this->info('>>> TESTE DO OPENSEARCH CONCLUÍDO COM SUCESSO! <<<');
            return 0;

        } catch (\Exception $e) {
            $this->error('### FALHA NA BUSCA ###');
            $this->error($e->getMessage());
            return 1;
        }
    }
}