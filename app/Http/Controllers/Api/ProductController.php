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
        
    }

    public function index(Request $request): JsonResponse
    {
        if ($request->has('search') && !empty($request->input('search'))) {
            $products = $this->repository->searchOnOpenSearch($request->input('search'));
        } else {
            $products = $this->repository->findAll();
        }

        return response()->json($products);
    }

    public function store(StoreProductRequest $request): JsonResponse
    {

        $product = new Product();
        $product->setName($request->input('name'));
        $product->setDescription($request->input('description'));
        $product->setPrice($request->input('price'));
        $product->setCategory($request->input('category'));

        $this->repository->save($product);

        return response()->json($product, 201); 
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

        return response()->json(null, 204); 
    }
}