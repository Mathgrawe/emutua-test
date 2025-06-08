<?php

namespace App\Http\Controllers\Api;

use App\Entities\Product;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Repositories\ProductRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(private ProductRepository $repository)
    {
        // Injeção de dependência do nosso repositório.
    }

    public function index(Request $request): JsonResponse
    {
        // Lida com a busca e com a listagem completa
        if ($request->has('search')) {
            $products = $this->repository->searchByNameOrDescription($request->input('search'));
        } else {
            $products = $this->repository->findAll();
        }

        return response()->json($products);
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        // A validação já aconteceu automaticamente pelo StoreProductRequest
        $product = new Product();
        $product->setName($request->input('name'));
        $product->setDescription($request->input('description'));
        $product->setPrice($request->input('price'));
        $product->setCategory($request->input('category'));

        $this->repository->save($product);

        return response()->json($product, 201); // 201 Created
    }

    public function show(int $id): JsonResponse
    {
        $product = $this->repository->find($id);

        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        return response()->json($product);
    }

    public function update(StoreProductRequest $request, int $id): JsonResponse
    {
        $product = $this->repository->find($id);

        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }
        
        // A validação também já aconteceu aqui
        $product->setName($request->input('name'));
        $product->setDescription($request->input('description'));
        $product->setPrice($request->input('price'));
        $product->setCategory($request->input('category'));

        $this->repository->save($product);

        return response()->json($product);
    }

    public function destroy(int $id): JsonResponse
    {
        $product = $this->repository->find($id);

        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        $this->repository->delete($product);

        return response()->json(null, 204); // 204 No Content
    }
}