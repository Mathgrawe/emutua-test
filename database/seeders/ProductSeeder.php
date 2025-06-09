<?php

namespace Database\Seeders;

use App\Entities\Product;
use App\Repositories\ProductRepository;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $repository = app(ProductRepository::class);

        $products = [
            ['name' => 'Laptop Pro X1', 'description' => 'Notebook de alta performance para profissionais.', 'price' => 9500.50, 'category' => 'Eletrônicos'],
            ['name' => 'Mouse Vertical Ergonômico', 'description' => 'Previne lesões por esforço repetitivo.', 'price' => 250.00, 'category' => 'Acessórios'],
            ['name' => 'Teclado Mecânico Gamer', 'description' => 'Switches blue para máxima resposta tátil.', 'price' => 550.75, 'category' => 'Acessórios'],
            ['name' => 'Monitor Ultrawide 34"', 'description' => 'Mais espaço para sua produtividade e jogos.', 'price' => 3200.00, 'category' => 'Monitores'],
            ['name' => 'Cadeira Gamer Confort', 'description' => 'Conforto para longas sessões de uso.', 'price' => 1800.00, 'category' => 'Móveis'],
            ['name' => 'SSD NVMe 1TB', 'description' => 'Armazenamento ultra-rápido para seus arquivos.', 'price' => 650.00, 'category' => 'Componentes'],
            ['name' => 'Webcam 4K com Autofoco', 'description' => 'Imagem de alta qualidade para suas reuniões.', 'price' => 899.90, 'category' => 'Acessórios'],
            ['name' => 'Headset 7.1 Surround', 'description' => 'Áudio imersivo para a melhor experiência.', 'price' => 750.00, 'category' => 'Áudio'],
        ];

        foreach ($products as $productData) {
            $product = new Product();
            $product->setName($productData['name']);
            $product->setDescription($productData['description']);
            $product->setPrice($productData['price']);
            $product->setCategory($productData['category']);

            $repository->save($product);
        }
    }
}