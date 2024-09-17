<?php

namespace App\Entity;

use App\Repository\CategoryRepository;
use App\Dto\CategoryDto;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Ignore;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
class Category
{
    #[Groups(['category_data', 'create_category', 'get_products', 'create_product'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['category_data', 'create_category', 'get_products', 'create_product'])]
    #[ORM\Column(length: 128)]
    private ?string $name = null;

    #[Groups(['category_data', 'create_category', 'get_products', 'create_product'])]
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[Groups(['category_data'])]
    #[ORM\OneToMany(targetEntity: Product::class, mappedBy: 'category', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $products;

    public function __construct()
    {
        $this->products = new ArrayCollection();
    }

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

    /**
     * @return Collection<int, Product>
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(Product $product): self
    {
        if (!$this->products->contains($product)) {
            $this->products[] = $product;
            $product->setCategory($this);
        }

        return $this;
    }

    public function removeProduct(Product $product): self
    {
        if ($this->products->contains($product)) {
            $this->products->removeElement($product);
            // set the owning side to null (unless already changed)
            if ($product->getCategory() === $this) {
                $product->setCategory(null);
            }
        }

        return $this;
    }

    /**
     * Create a new category from a given CategoryDto
     * 
     * @param CategoryDto $categoryDto
     * 
     * @return Category
     */
    public static function createFromDto(CategoryDto $categoryDto): self
    {
        $category = new self();

        $category
            ->setName($categoryDto->name)
            ->setDescription($categoryDto->description);

        return $category;
    }
}
