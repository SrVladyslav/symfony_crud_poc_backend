<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Repository\CategoryRepository;
use App\Dto\ProductDto;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    /**
    * @return Product[] Returns an array of Product objects
    */
    public function getProducts(): array
    {
        try {
            return $this->findAll() ?? [];
        } catch(\Exception $e) {
            return [];
        }
    }

    /**
     * Create a new product
     * 
     * @param string $name
     * @param string $description
     * @param float $price
     * 
     * @return Product|null
     */
    public function createProduct( ProductDto $productDto, CategoryRepository $categoryRepository ): Product|null
    {
        try{
            $newProduct = Product::createFromDto($productDto, $categoryRepository);
        } catch(\Exception $e){
            return null;
        }

        $entityManager = $this->getEntityManager();
        $entityManager->persist($newProduct);
        $entityManager->flush();

        return $newProduct ?? null;
    }

    /**
     * Updates the product given the new cata
     * 
     * @param string $name
     * @param string $description
     * @param float $price
     * 
     * @return Product|null
     */
    public function updateProduct(
        Product $product, 
        ProductDto $productDto, 
        CategoryRepository $categoryRepository 
    ): Product|null
    {
        if(
            $product->getName() === $productDto->name &&
            $product->getDescription() === $productDto->description &&
            $product->getPrice() === $productDto->price &&
            $product->getCategory()->getId() === $productDto->categoryId
        ) {
            // Do not update the DB if the data is the same
            return $product;
        }

        // Updating the product data
        $product 
            ->setName($productDto->name ?? $product->getName())
            ->setDescription($productDto->description ?? $product->getDescription())
            ->setPrice($productDto->price ?? $product->getPrice());

        // update the item category if this is changed, otherwise stay with the old one
        if( isset($productDto->categoryId) && $product->getCategory()->getId() !== $productDto->categoryId ) {
            $newCategory = $categoryRepository->find($productDto->categoryId);

            if(empty($newCategory)) {
                throw new \Exception('Category not found', 404);
            }

            // If category exists, update the product category
            $product->setCategory($newCategory);
        }

        try{
            // Updating the product
            $entityManager = $this->getEntityManager();
            $entityManager->persist($product);
            $entityManager->flush();
    
            return $product ?? null;
        } catch(\Exception $e) {
            throw new \Exception('Error updating the product', 500);
        }
    }

    /**
     * Delete a given product
     * 
     * @param Product $product
     * 
     * @return bool Returns true if the product was deleted, false otherwise
     */
    public function deleteProduct( ?Product $product ): bool
    {
        if(empty($product)) {
            return false;
        }

        try {   
            $entityManager = $this->getEntityManager();
            $entityManager->remove($product);
            $entityManager->flush();

            return true;
        }catch(Exception $e) {
            return false;
        }
    }
}
