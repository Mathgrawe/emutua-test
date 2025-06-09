<?php

namespace App\Repositories;

use App\Entities\Product;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use OpenSearch\Client;
use Illuminate\Support\Facades\Log;

class ProductRepository
{
    private EntityRepository $repository;
    private Client $opensearch;

    public function __construct(
        private EntityManagerInterface $em,
        Client $opensearchClient
    ) {
        $this->repository = $em->getRepository(Product::class);
        $this->opensearch = $opensearchClient;
    }

    public function find(int $id): ?Product
    {
        return $this->repository->find($id);
    }

    public function findAll(): array
    {
        return $this->repository->findAll();
    }

    public function save(Product $product): void
    {
        $this->em->persist($product);
        $this->em->flush();
        $this->index($product);
    }

    public function delete(Product $product): void
    {
        $productId = $product->getId();

        $this->em->remove($product);
        $this->em->flush();

        $this->opensearch->delete([
            'index' => 'products',
            'id' => $productId,
        ]);
    }

    private function index(Product $product): void
    {
        $this->opensearch->index([
            'index' => 'products', 
            'id'    => $product->getId(),
            'body'  => [
                'name' => $product->getName(),
                'description' => $product->getDescription(),
                'category' => $product->getCategory(),
            ]
        ]);
    }

    public function searchByNameOrDescription(string $term): array
    {
        return $this->repository->createQueryBuilder('p')
            ->where('p.name LIKE :term')
            ->orWhere('p.description LIKE :term')
            ->setParameter('term', '%' . $term . '%')
            ->getQuery()
            ->getResult();
    }

    public function searchOnOpenSearch(string $term): array
    {

        Log::info("[REPO] Iniciando busca no OpenSearch pelo termo: " . $term);

        $params = [
            'index' => 'products',
            'body'  => [
                'query' => [
                    'multi_match' => [
                        'query' => $term,
                        'fields' => ['name', 'description', 'category']
                    ]
                ]
            ]
        ];

        
        Log::info("[REPO] Parâmetros da busca montados:", $params);
        
        try {
            $response = $this->opensearch->search($params);
            Log::info("[REPO] Resposta do OpenSearch recebida.");
        } catch (\Exception $e) {
            Log::error("[REPO] ERRO ao buscar no OpenSearch: " . $e->getMessage());
            // Lança a exceção de novo para que o Laravel a capture e mostre o erro 500
            throw $e;
        }
    
        Log::info("[REPO] Mapeando resultados...");
        return array_map(function ($hit) {
            // Remove a chave '_source' e retorna apenas os dados do produto
            $productData = $hit['_source'];
            // Adiciona o ID do OpenSearch ao resultado para consistência
            $productData['id'] = $hit['_id']; 
            return $productData;
        }, $response['hits']['hits']);
    }
}