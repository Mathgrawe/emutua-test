<?php

use App\Http\Controllers\Api\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Rotas da nossa API de Produtos
Route::apiResource('products', ProductController::class);