<?php

namespace App\Repositories;

use App\Entities\Product;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Illuminate\Support\Facades\Log;
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
            'id'    => $productId,
        ]);
    }

    /**
     * Normaliza e remove acentos de uma string.
     */
    private function normalizeString(string $str): string
    {
        $normalized = transliterator_transliterate('Any-Latin; Latin-ASCII; [\u0080-\u7fff] remove', $str);
        return strtolower($normalized);
    }

    private function index(Product $product): void
    {
        $this->opensearch->index([
            'index' => 'products',
            'id'    => $product->getId(),
            'body'  => [
                'name'               => $product->getName(),
                'description'        => $product->getDescription(),
                'category'           => $product->getCategory(),
                'name_normalized'    => $this->normalizeString($product->getName()),
                'category_normalized'=> $this->normalizeString($product->getCategory()),
                'description_normalized' => $this->normalizeString($product->getDescription()),
            ]
        ]);
    }

    /**
     * Busca produtos no OpenSearch com relevância, busca parcial e sem acentuação.
     */
    public function searchOnOpenSearch(string $term): array
    {
        $normalizedTerm = $this->normalizeString($term);

        Log::info("[REPO] Iniciando busca AVANÇADA no OpenSearch pelo termo normalizado: " . $normalizedTerm);

        $params = [
            'index' => 'products',
            'body'  => [
                'query' => [
                    'bool' => [
                        'should' => [
                            [
                                'multi_match' => [
                                    'query'  => $normalizedTerm,
                                    'type'   => 'best_fields',
                                    'fields' => [
                                        'name_normalized^3',
                                        'category_normalized^2',
                                        'description_normalized'
                                    ],
                                ],
                            ],
                            [
                                'multi_match' => [
                                    'query'  => $normalizedTerm,
                                    'type'   => 'phrase_prefix',
                                    'fields' => ['name_normalized', 'category_normalized', 'description_normalized'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        try {
            $response = $this->opensearch->search($params);
        } catch (\Exception $e) {
            Log::error("[REPO] ERRO ao buscar no OpenSearch: " . $e->getMessage());
            throw $e;
        }

        return array_map(function ($hit) {
            $productData = $hit['_source'];
            $productData['id'] = (int) $hit['_id'];
            return $productData;
        }, $response['hits']['hits']);
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
}
