<?php

namespace App\Repository;

use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Dto\CategoryDto;
use Doctrine\ORM\Tools\Pagination\Paginator;

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
     * Retrieves a paginated list of categories.
     * 
     * @param int $page  The current page number.
     * @param int $limit The number of categories to retrieve per page.
     * 
     * @return Paginator A paginated list of categories ordered by name in ascending order.
     */
    public function getPaginatedCategories(int $page, int $limit): Paginator
    {
        $query = $this->createQueryBuilder('c')
            ->orderBy('c.name', 'ASC')
            ->getQuery();

        $query->setFirstResult($limit * ($page - 1))
              ->setMaxResults($limit);

        return new Paginator($query, true);
    }

    /**
     * Updates a category entity with new data if changes are detected.
     * 
     * This method compares the current category data with the new data and only
     * persists the changes if they differ. If the data remains unchanged, it skips
     * the update. Returns the updated category on success, or null if an error occurs.
     * 
     * @param Category $category The current category entity to be updated.
     * @param CategoryDto $newCategoryData The new category data.
     * 
     * @return Category|null Returns the updated category or null if an error occurs.
     */
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
     * Creates a new category from the provided data and persists it to the database.
     * 
     * @param CategoryDto $categoryDto The data transfer object containing category details.
     * 
     * @return Category The newly created category.
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
     * Deletes the specified category from the database.
     * 
     * @param Category|null $category The category to delete, or null if not found.
     * 
     * @return bool True if the category was successfully deleted, false otherwise.
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
