<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Repository\CategoryRepository;
use App\Dto\ProductDto;
use Doctrine\ORM\Tools\Pagination\Paginator;

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
     * Retrieves a paginated list of products.
     * 
     * @param int $page  The current page number.
     * @param int $limit The number of products to retrieve per page.
     * 
     * @return Paginator A paginated list of products ordered by name in ascending order.
     */
    public function getPaginatedProducts(int $page, int $limit): Paginator
    {
        $query = $this->createQueryBuilder('p')
            ->orderBy('p.name', 'ASC')
            ->getQuery();

        $query->setFirstResult($limit * ($page - 1))
              ->setMaxResults($limit);

        return new Paginator($query, true);
    }

    /**
     * Creates a new product based on the provided ProductDto and category repository.
     * 
     * @param ProductDto $productDto Data transfer object containing product details.
     * @param CategoryRepository $categoryRepository Repository for managing category data.
     * 
     * @return Product|null The newly created product, or null if an error occurred.
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
     * Updates an existing product with the provided data.
     * 
     * @param Product $product The product entity to be updated.
     * @param ProductDto $productDto Data transfer object containing new product details.
     * @param CategoryRepository $categoryRepository Repository for category management.
     * 
     * @return Product|null The updated product, or null if no changes were made or an error occurred.
     * 
     * @throws \Exception If the category is not found or an error occurs during the update process.
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
     * Deletes a product from the database.
     * 
     * @param Product|null $product The product entity to be deleted.
     * 
     * @return bool True if the deletion was successful, false otherwise.
     * 
     * @throws Exception If an error occurs during the deletion process.
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
