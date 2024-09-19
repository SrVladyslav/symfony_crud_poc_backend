<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\CategoryRepository;
use App\Service\TokenService;
use App\Dto\CategoryDto;
use App\Entity\Category;

// Documentation
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Annotation\Model;
use App\Dto\Responses\GetAllCategoriesDto;
use App\Dto\Responses\GetCategoryDto;
use App\Dto\Responses\ErrorDto;
use App\Dto\Responses\DeleteDto;

// Pagination
use Doctrine\ORM\Tools\Pagination\Paginator;


#[Route('/api/categories', name: 'api_categories')]
class CategoryController extends AbstractController
{
    private TokenService $tokenService;

    public function __construct()
    {
        $this->tokenService = new TokenService($_ENV['API_TOKEN'] ?? '');
    }

    // ============================================================================================================================ GET
    /**
     * Retrieves all categories along with their associated products using pagination. Max items limit per page is 50.
     * 
     * This endpoint supports pagination through query parameters. This endpoint requires a valid authentication token.
     * 
     * @param CategoryRepository $categories The category repository.
     * @param Request $request The HTTP request containing the auth token.
     * 
     * @return Response JSON response with status, message, and category data.
     *                  Returns unauthorized if the token is invalid.
     * 
     */
    #[OA\Response(response: Response::HTTP_INTERNAL_SERVER_ERROR, description: "Server Error", content: new OA\JsonContent(ref: new Model(type: ErrorDto::class)))]
    #[OA\Response(response: Response::HTTP_UNAUTHORIZED, description: "Unauthorized", content: new OA\JsonContent(ref: new Model(type: ErrorDto::class)))]
    #[OA\Response(response: Response::HTTP_NOT_FOUND, description: "Not Found", content: new OA\JsonContent(ref: new Model(type: ErrorDto::class)))]
    #[OA\Response(
        response: Response::HTTP_OK, description: 'Successful response with a list of categories and their products',
        content: new OA\JsonContent(ref: new Model(type: GetAllCategoriesDto::class))
    )]
    #[OA\Parameter(name: "page",in: "query", description: "The page number of the current page.", required: false, schema: new OA\Schema(type: "integer"))]
    #[OA\Parameter(name: "limit",in: "query", description: "The maximum number of items per page.", required: false, schema: new OA\Schema(type: "integer"))]
    #[Route('/get', name: 'get_all', methods: ['GET'], format: 'json')]
    public function getAllCategories(CategoryRepository $categoryRepository, Request $request): Response
    {
        // Authorization
        if (!$this->tokenService->isValidToken($request)) {
            return $this->json([
                'status'=> 'error',
                'message' => 'Invalid token',
            ], Response::HTTP_UNAUTHORIZED);
        }

        // TODO: In a full prod project, we should add some more validations and personalized error handling: e.g. validate strings, so on... 
        //       But for now we will keep it simple.

        // Pagination parameters
        $maxLimit = isset($_ENV['PAGINATION_MAX_LIMIT']) ? (int)$_ENV['MAX_LIMIT'] : 50;
        $page = (int) $request->query->get('page', 1);
        $limit = (int) $request->query->get('limit', $maxLimit);
        // Ensure the limit does not exceed the maximum limit
        $limit = min($limit, $maxLimit);
        // Ensure the limit is at least 1
        $limit = max(1, $limit);

        try{
            $paginator = $categoryRepository->getPaginatedCategories($page, $limit);

            $totalItems = count($paginator);
            $totalPages = ceil($totalItems / $limit);

            // Build pagination links
            $prevPage = $page > 1 ? '/api/categories/get?page=' . ($page - 1) . '&limit=' . $limit : null;
            $nextPage = $page < $totalPages ? '/api/categories/get?page=' . ($page + 1) . '&limit=' . $limit : null;

            // Prepare the response DTO
            return $this->json(new GetAllCategoriesDto(
                status: 'success',
                message: 'Found successfully',
                page: (string)$page,
                limit: (string)$limit,
                totalPages: (string)$totalPages,
                prevPage: $prevPage,
                nextPage: $nextPage,
                data: $paginator->getIterator()->getArrayCopy()
            ), Response::HTTP_OK);

        } catch (\Exception $e) {
            return $this->json([
                'status' => 'error',
                'message' => 'An error occurred: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    // ============================================================================================================================ GET BY ID
    /**
     * Retrieves a specific category by its ID.
     *
     * This endpoint requires a valid authentication token and expects a categoryID param in the URL.
     * 
     * @param CategoryRepository $categories The repository to fetch categories.
     * @param Request $request The HTTP request containing the authorization token.
     * @param Category|null $category The category to retrieve, or null if not found.
     *
     * @return Response JSON response containing the category details if found,
     *                   or an error message if the category is not found or token is invalid.
     *
     * Note: The response includes status and message fields, with category details if successful.
     */
    #[OA\Response(response: Response::HTTP_INTERNAL_SERVER_ERROR, description: "Server Error", content: new OA\JsonContent(ref: new Model(type: ErrorDto::class)))]
    #[OA\Response(response: Response::HTTP_UNAUTHORIZED, description: "Unauthorized", content: new OA\JsonContent(ref: new Model(type: ErrorDto::class)))]
    #[OA\Response(response: Response::HTTP_NOT_FOUND, description: "Not Found", content: new OA\JsonContent(ref: new Model(type: ErrorDto::class)))]
    #[OA\Response(
        response: Response::HTTP_OK, description: 'Successful response with a list of categories and their products',
        content: new OA\JsonContent(ref: new Model(type: GetCategoryDto::class))
    )]
    #[Route('/{category}/get', name: 'get_by_id', methods: ['GET'], format: 'json')]
    public function getCategoryById(
        CategoryRepository $categories, 
        Request $request,
        ?Category $category
    ): Response {
        // Authorization
        if (!$this->tokenService->isValidToken($request)) {
            return $this->json([
                'status'=> 'error',
                'message' => 'Invalid token',
            ], Response::HTTP_UNAUTHORIZED);
        }

        // Category Existence Validator
        if(empty($category)) {
            return $this->json([
                'status'=> 'error',
                'message' => 'Category not found',
            ], Response::HTTP_NOT_FOUND);
        }

        // Serialize and get category by ID, without using DTOs
        return $this->json([
            'status'=> 'success',
            'message'=> 'Found successfully',
            'data'=>$category
        ], Response::HTTP_OK, context: [
            AbstractNormalizer::GROUPS => ['category_data'],
        ]);
    }

    // ============================================================================================================================ POST
    /**
     * Creates a new category given the name and description.
     * 
     * This endpoint requires a valid authentication token and expects a request body with the category details.
     * Upon successful creation, it returns the new category's ID, name, and description.
     * 
     * @param CategoryDto $categoryDto The category data to create.
     * @param Request $request The HTTP request, which should include an authorization token.
     * @param CategoryRepository $category The repository for handling category operations.
     * 
     * @return Response The HTTP response containing the newly created category data or an error message.
     * 
     * Note: Here we will use the group 'create_category' to serialize the newly created category instead of a DTO object.
     */
    #[OA\Response(response: Response::HTTP_INTERNAL_SERVER_ERROR, description: "Server Error", content: new OA\JsonContent(ref: new Model(type: ErrorDto::class)))]
    #[OA\Response(response: Response::HTTP_UNAUTHORIZED, description: "Unauthorized", content: new OA\JsonContent(ref: new Model(type: ErrorDto::class)))]
    #[OA\Response(response: Response::HTTP_NOT_FOUND, description: "Not Found", content: new OA\JsonContent(ref: new Model(type: ErrorDto::class)))]
    #[OA\RequestBody(description: "To create a new category, you need to provide the following data:", content: new OA\JsonContent(ref: new Model(type: CategoryDto::class)))]
    #[OA\Response(
        response: Response::HTTP_OK, description: 'Successful response with the newly created category.',
        content: new OA\JsonContent(ref: new Model(type: Category::class, groups: ['create_category']))
    )]
    #[Route('/create', name: 'create_category', methods: ['POST'], format: 'json', )]
    public function createCategory(
        #[MapRequestPayload] CategoryDto $categoryDto,
        Request $request,
        CategoryRepository $category
    ): Response
    {
        // Authorization
        if (!$this->tokenService->isValidToken($request)) {
            return $this->json([
                'status'=> 'error',
                'message' => 'Invalid token',
            ], Response::HTTP_UNAUTHORIZED);
        }

        /**
         * TODO: Create some custom validator for error messages, here we are 
         * using the default Asserts in category DTO, but the messages 
         * contain the full content.
         **/ 

        // Create new category
        $newCategory = $category->createCategory($categoryDto);

        // We only serialize the ID, NAME and DESCRIPTION fields
        return $this->json($newCategory, Response::HTTP_OK, context: [
            AbstractNormalizer::GROUPS => ['create_category'],
        ]);
    }

    // ============================================================================================================================ PUT
    /**
     * Updates an existing category given the ID.
     * 
     * This endpoint requires a valid authentication token. To update the category specified by its ID, provide all required data in the request body.
     * 
     * @param CategoryDto $categoryDto The updated category data.
     * @param CategoryRepository $categoryRepo The repository for handling category operations.
     * @param Request $request The HTTP request, which should include an authorization token.
     * @param Category|null $category The category to be updated.
     * 
     * @return Response The HTTP response containing the updated category data or an error message.
     * 
     */
    #[OA\Response(response: Response::HTTP_INTERNAL_SERVER_ERROR, description: "Server Error", content: new OA\JsonContent(ref: new Model(type: ErrorDto::class)))]
    #[OA\Response(response: Response::HTTP_UNAUTHORIZED, description: "Unauthorized", content: new OA\JsonContent(ref: new Model(type: ErrorDto::class)))]
    #[OA\Response(response: Response::HTTP_NOT_FOUND, description: "Not Found", content: new OA\JsonContent(ref: new Model(type: ErrorDto::class)))]
    #[OA\RequestBody(description: "In order to update the category, you need to provide all the required data.", content: new OA\JsonContent(ref: new Model(type: CategoryDto::class)))]
    #[OA\Response(
        response: Response::HTTP_OK, description: 'Successful response with the newly updated category.',
        content: new OA\JsonContent(ref: new Model(type: Category::class, groups: ['create_category']))
    )]
    #[Route('/{id}/update', name: 'update_category', methods: ['PUT'], format: 'json')]
    public function updateCategory(
        #[MapRequestPayload] CategoryDto $categoryDto,
        CategoryRepository $categoryRepo,
        Request $request,
        ?Category $category
    ): Response
    {
        // Authorization
        if (!$this->tokenService->isValidToken($request)) {
            return $this->json([
                'status'=> 'error',
                'message' => 'Invalid token',
            ], Response::HTTP_UNAUTHORIZED);
        }

        // Category Existence Basic Validator
        if(empty($category)) {
            return $this->json([
                'message' => 'Category not found',
            ], Response::HTTP_NOT_FOUND);
        }

        /**
         * TODO: Create some custom validator for error messages, here we are 
         * using the default Asserts in category DTO, but the messages 
         * contain the full content.
         **/ 

        $updatedCategory = $categoryRepo->updateCategory($category, $categoryDto);
        if(empty($updatedCategory)) {
            return $this->json([
                'message' => 'Something went wrong.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->json($updatedCategory, Response::HTTP_OK, context: [
            AbstractNormalizer::GROUPS => ['create_category']
        ]);
    }

    // ============================================================================================================================ DELETE
    /**
     * Deletes a category by its ID.
     *
     * This endpoint requires a valid authentication token in the request header.
     * Returns a success message if the category is deleted, or an error message if the category is not found.
     *
     * @param Request $request The HTTP request containing the authorization token.
     * @param CategoryRepository $categoryRepo Repository for handling category operations.
     * @param Category|null $category The category to be deleted, identified by its ID.
     *
     * @return Response JSON response with status and message.
     */
    #[OA\Response(response: Response::HTTP_INTERNAL_SERVER_ERROR, description: "Server Error", content: new OA\JsonContent(ref: new Model(type: ErrorDto::class)))]
    #[OA\Response(response: Response::HTTP_UNAUTHORIZED, description: "Unauthorized", content: new OA\JsonContent(ref: new Model(type: ErrorDto::class)))]
    #[OA\Response(response: Response::HTTP_NOT_FOUND, description: "Not Found", content: new OA\JsonContent(ref: new Model(type: ErrorDto::class)))]
    #[OA\Response(
        response: Response::HTTP_OK, description: 'Successful response with the given category deleted.',
        content: new OA\JsonContent(ref: new Model(type: DeleteDto::class))
    )]
    #[Route('/{category}/delete', name: 'delete_category', methods: ['DELETE'], format: 'json')]
    public function deleteCategory(
        Request $request,
        CategoryRepository $categoryRepo, 
        ?Category $category
    ): Response
    {
        // Authorization
        if (!$this->tokenService->isValidToken($request)) {
            return $this->json([
                'status'=> 'error',
                'message' => 'Invalid token',
            ], Response::HTTP_UNAUTHORIZED);
        }

        /**
         * TODO: e.g. Create to check if a given user with this token can delete
         * this category
         **/ 

        // Delete Category
        $isDeleted = $categoryRepo->deleteCategory($category);

        return $this->json([
            'status'=> $isDeleted ? 'success' : 'error',
            'message' => $isDeleted ? 'Category deleted successfully' : 'Category not found',
        ], $isDeleted ? Response::HTTP_OK : Response::HTTP_NOT_FOUND);
    }
}
