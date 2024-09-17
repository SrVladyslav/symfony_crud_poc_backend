<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use Doctrine\DBAL\Types\Types;
use App\Dto\ProductDto;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use OpenApi\Attributes as OA;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[Groups(['get_products', 'category_data', 'create_product'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['get_products', 'category_data', 'create_product'])]
    #[ORM\Column(length: 128)]
    private ?string $name = null;

    #[Groups(['get_products', 'category_data', 'create_product'])]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[Groups(['get_products', 'category_data', 'create_product'])]
    #[ORM\Column]
    private ?float $price = null;

    #[Groups(['get_products', 'create_product'])]
    #[ORM\ManyToOne(targetEntity: Category::class, inversedBy: 'products')]
    #[ORM\JoinColumn(name: 'category_id', referencedColumnName: 'id', onDelete:"CASCADE")]
    private ?Category $category = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    public static function createFromDto(
        ProductDto $productDto, 
        CategoryRepository $categoryRepository
    ): self
    {
        $product = new self();

        $category = $categoryRepository->find($productDto->categoryId);

        // We suppose that each product should have a category
        if (!$category) {
            throw new \Exception('Category not found');
        }

        $product
            ->setName($productDto->name)
            ->setDescription($productDto->description)
            ->setPrice($productDto->price)
            ->setCategory($category);

        return $product;
    }
}
