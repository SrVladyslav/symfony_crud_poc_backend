<?php

namespace App\Repository;

use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Dto\CategoryDto;

/**
 * @extends ServiceEntityRepository<Category>
 */
class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    /**
    * @return Category[] Returns an array of Category objects
    
    */
    public function getCategories(): array
    {
        return $this->findAll() ?? [];
    }

    public function updateCategory( 
        Category $category, 
        CategoryDto $newCategoryData 
    ): Category|null 
    {
        if(
            $category->getName() == $newCategoryData->name &&
            $category->getDescription() == $newCategoryData->description    
        ) {
            // Do not update the DB if the data is the same
            return $category;
        }
        
        $category
            ->setName($newCategoryData->name)
            ->setDescription($newCategoryData->description);

        try {
            $entityManager = $this->getEntityManager();
            $entityManager->persist($category);
            $entityManager->flush();
    
            return $category;
        } catch(Exception $e) {
            return null;
        }
    }

    /**
     * Create a new category
     * 
     * @param string $name
     * @param string $description
     * 
     * @return Category|null
     * 
     */
    public function createCategory( CategoryDto $categoryDto ): Category
    {
        $newCategory = Category::createFromDto($categoryDto);

        $entityManager = $this->getEntityManager();
        $entityManager->persist($newCategory);
        $entityManager->flush();

        return $newCategory ?? null;
    }

    /**
     * Delete a given category
     * 
     * @param Category $category
     * 
     * @return bool Returns true if the category was deleted, false otherwise
     * 
     */
    public function deleteCategory( ?Category $category ): bool
    {
        if(empty($category)) {
            return false;
        }

        try {   
            $entityManager = $this->getEntityManager();
            $entityManager->remove($category);
            $entityManager->flush();

            return true;
        }catch(Exception $e) {
            return false;
        }
    }
}
