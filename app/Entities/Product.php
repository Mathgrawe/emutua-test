<?php

namespace App\Entities;

use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

/**
 * @ORM\Entity
 * @ORM\Table(name="products")
 */
class Product implements JsonSerializable 
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected int $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected string $name;

    /**
     * @ORM\Column(type="text")
     */
    protected string $description;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    protected float $price;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected string $category;

    // --- Getters ---
    public function getId(): int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getDescription(): string { return $this->description; }
    public function getPrice(): float { return $this->price; }
    public function getCategory(): string { return $this->category; }

    // --- Setters ---
    public function setName(string $name): void { $this->name = $name; }
    public function setDescription(string $description): void { $this->description = $description; }
    public function setPrice(float $price): void { $this->price = $price; }
    public function setCategory(string $category): void { $this->category = $category; }

    /**
     * "manual de instruções" para converter em JSON.
     */
    public function jsonSerialize(): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'description' => $this->description,
            'price'       => $this->price,
            'category'    => $this->category,
        ];
    }
}