<?php

namespace App\Repositories;

use App\Entities\Product;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use OpenSearch\Client;

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

        $response = $this->opensearch->search($params);

        // O código abaixo apenas extrai os resultados do formato de resposta do OpenSearch
        return array_map(function ($hit) {
            return $hit['_source'];
        }, $response['hits']['hits']);
    }
}