<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use App\Service\TokenService;
use App\Dto\ProductDto;
use App\Entity\Product;

// Documentation
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Annotation\Model;
use App\Dto\Responses\UpdateProductDto;
use App\Dto\Responses\GetProductDto;
use App\Dto\Responses\GetAllProductsDto;
use App\Dto\Responses\ErrorDto;
use App\Dto\Responses\DeleteDto;

// Pagination
use Doctrine\ORM\Tools\Pagination\Paginator;

#[Route('api/products', name: 'api_products')]
class ProductController extends AbstractController
{
    private TokenService $tokenService;

    public function __construct()
    {
        $this->tokenService = new TokenService($_ENV['API_TOKEN'] ?? '');
    }

    // ============================================================================================================================ GET
    /**
     * Retrieves all products along with their categories using pagination.
     * 
     * This endpoint returns a list of all products with pagination details. 
     * The response includes the products and their associated categories, 
     * with fields like page, limit, and total pages. If the request has an invalid 
     * authorization token or if there are no products found, an appropriate error 
     * response is returned.
     * 
     * @param ProductRepository $productRepository Repository to fetch product data.
     * @param Request $request The current HTTP request.
     * @return Response JSON response with the list of products and pagination information.
     */
    #[OA\Response(response: Response::HTTP_INTERNAL_SERVER_ERROR, description: "Server Error", content: new OA\JsonContent(ref: new Model(type: ErrorDto::class)))]
    #[OA\Response(response: Response::HTTP_UNAUTHORIZED, description: "Unauthorized", content: new OA\JsonContent(ref: new Model(type: ErrorDto::class)))]
    #[OA\Response(response: Response::HTTP_NOT_FOUND, description: "Not Found", content: new OA\JsonContent(ref: new Model(type: ErrorDto::class)))]
    #[OA\Response(
        response: Response::HTTP_OK, description: 'Successful response with a list of products with their categories.',
        content: new OA\JsonContent(ref: new Model(type: GetAllProductsDto::class))
    )]
    #[OA\Parameter(name: "page",in: "query", description: "The page number of the current page.", required: false, schema: new OA\Schema(type: "integer"))]
    #[OA\Parameter(name: "limit",in: "query", description: "The maximum number of items per page.", required: false, schema: new OA\Schema(type: "integer"))]
    #[Route('/get', name: 'get_products', methods: ['GET'], format: 'json')]
    public function getAllProducts(ProductRepository $productRepository, Request $request): Response
    {
        // Authorization check: Extracts the token from the Authorization header and verifies its validity
        if (!$this->tokenService->isValidToken($request)) {
            return $this->json([
                'status'=> 'error',
                'message' => 'Invalid token',
            ], Response::HTTP_UNAUTHORIZED);
        }

        // TODO: In a full prod project, we should add some more validations and personalized error handling: e.g. validate strings, so on... 
        //       But for now we will keep it simple.

        // Pagination parameters
        $page = (int) $request->query->get('page', 1);
        $limit = (int) $request->query->get('limit', 3);
        $limit = max(1, $limit);

        try {
            $paginator = $productRepository->getPaginatedProducts($page, $limit);

            $totalItems = count($paginator);
            $totalPages = ceil($totalItems / $limit);

            // Build pagination links
            $prevPage = $page > 1 ? '/api/products/get?page=' . ($page - 1) . '&limit=' . $limit : null;
            $nextPage = $page < $totalPages ? '/api/products/get?page=' . ($page + 1) . '&limit=' . $limit : null;

            // Prepare the response DTO
            return $this->json(new GetAllProductsDto(
                status: 'success',
                message: 'Found successfully',
                page: (string)$page,
                limit: (string)$limit,
                totalPages: (string)$totalPages,
                prevPage: $prevPage,
                nextPage: $nextPage,
                data: $paginator->getIterator()->getArrayCopy()
            ), Response::HTTP_OK, context: [
                AbstractNormalizer::GROUPS => ['get_products'],
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'status' => 'error',
                'message' => 'An error occurred: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // ============================================================================================================================ GET BY ID
    /**
     * Retrieves a product by its ID.
     * 
     * This endpoint requires a valid authorization token. It returns the product details 
     * if found, otherwise an error message is provided.
     * 
     * @param ProductRepository $productRepository Repository for managing product operations.
     * @param Request $request HTTP request, including the authorization token.
     * @param Product|null $product The product entity to be retrieved.
     * 
     * @return Response JSON response containing the product details or an error message.
     */
    #[OA\Response(response: Response::HTTP_INTERNAL_SERVER_ERROR, description: "Server Error", content: new OA\JsonContent(ref: new Model(type: ErrorDto::class)))]
    #[OA\Response(response: Response::HTTP_UNAUTHORIZED, description: "Unauthorized", content: new OA\JsonContent(ref: new Model(type: ErrorDto::class)))]
    #[OA\Response(response: Response::HTTP_NOT_FOUND, description: "Not Found", content: new OA\JsonContent(ref: new Model(type: ErrorDto::class)))]
    #[OA\Response(
        response: Response::HTTP_OK, description: 'Successful response with a list of products',
        content: new OA\JsonContent(ref: new Model(type: GetProductDto::class))
    )]
    #[Route('/{product}/get', name: 'get_product_by_id', methods: ['GET'], format: 'json')]
    public function getProductById(
        ProductRepository $productRepository, 
        Request $request,
        ?Product $product
    ): Response {
        // Authorization check: Extracts the token from the Authorization header and verifies its validity
        if (!$this->tokenService->isValidToken($request)) {
            return $this->json([
                'status'=> 'error',
                'message' => 'Invalid token',
            ], Response::HTTP_UNAUTHORIZED);
        }

        // Product Existence Validator
        if(empty($product)) {
            return $this->json([
                'status'=> 'error',
                'message' => 'Product not found',
            ], Response::HTTP_NOT_FOUND);
        }

        // Serialize and get product by ID
        return $this->json([
            'status'=> 'success',
            'message'=> 'Found successfully',
            'data'=>$product
        ], Response::HTTP_OK, context: [
            AbstractNormalizer::GROUPS => ['get_products'],
        ]);
    }

    // ============================================================================================================================ POST
    /**
     * Creates a new product in the system.
     * 
     * This endpoint requires a valid authorization token. 
     * You must provide the required product details (name, description, price, categoryId) 
     * in the request body to successfully create a product.
     * 
     * @param ProductDto $productDto The product data for creation.
     * @param Request $request HTTP request, including the authorization token.
     * @param ProductRepository $product Repository for managing product operations.
     * @param CategoryRepository $categoryRepository Repository for managing category operations.
     * 
     * @return Response JSON response with the newly created product details or an error message.
     */
    #[OA\Response(response: Response::HTTP_INTERNAL_SERVER_ERROR, description: "Server Error", content: new OA\JsonContent(ref: new Model(type: ErrorDto::class)))]
    #[OA\Response(response: Response::HTTP_UNAUTHORIZED, description: "Unauthorized", content: new OA\JsonContent(ref: new Model(type: ErrorDto::class)))]
    #[OA\Response(response: Response::HTTP_NOT_FOUND, description: "Not Found", content: new OA\JsonContent(ref: new Model(type: ErrorDto::class)))]
    #[OA\RequestBody(description: "To create a new product, you need to provide the following data (name, description, price, categoryId):", content: new OA\JsonContent(ref: new Model(type: ProductDto::class)))]
    #[OA\Response(
        response: Response::HTTP_OK, description: 'Successful response with the newly created product.',
        content: new OA\JsonContent(ref: new Model(type: Product::class, groups: ['create_product']))
    )]
    #[Route('/create', name: 'create_product', methods: ['POST'], format: 'json')]
    public function createProduct(
        #[MapRequestPayload] ProductDto $productDto,
        Request $request,
        ProductRepository $product,
        CategoryRepository $categoryRepository
    ): Response
    {
        // Authorization check: Extracts the token from the Authorization header and verifies its validity
        if (!$this->tokenService->isValidToken($request)) {
            return $this->json([
                'status'=> 'error',
                'message' => 'Invalid token',
            ], Response::HTTP_UNAUTHORIZED);
        }

        /**
         * TODO: Create some custom validator for error messages, here we are 
         * using the default Asserts in product DTO, but the messages 
         * contain the full content.
         **/ 

        // Create new product
        $newProduct = $product->createProduct($productDto, $categoryRepository);

        if(empty($newProduct)) {
            return $this->json([
                'status'=> 'error',
                'message'=> 'An error occurred while creating the product',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // We only serialize the ID, NAME and DESCRIPTION fields
        return $this->json($newProduct, Response::HTTP_OK, context: [
            AbstractNormalizer::GROUPS => ['get_products'],
        ]);
    }
    
    // ============================================================================================================================ PUT
    /**
     * Updates an existing product by its ID.
     * 
     * This endpoint requires authorization via a valid token in the request headers.
     * The request body must include the product data (name, description, price, and categoryId).
     * 
     * @param ProductDto $productDto Data to update the product with.
     * @param CategoryRepository $categoryRepository Used to validate the product's category.
     * @param Request $request The HTTP request containing headers and body.
     * @param ProductRepository $productRepo Used to update the product.
     * @param Product|null $product The product to be updated, identified by its ID in the URL.
     * 
     * @return Response Returns a JSON response with the updated product data on success, 
     * or an error message if something went wrong.
     */
    #[OA\Response(response: Response::HTTP_INTERNAL_SERVER_ERROR, description: "Server Error", content: new OA\JsonContent(ref: new Model(type: ErrorDto::class)))]
    #[OA\Response(response: Response::HTTP_UNAUTHORIZED, description: "Unauthorized", content: new OA\JsonContent(ref: new Model(type: ErrorDto::class)))]
    #[OA\Response(response: Response::HTTP_NOT_FOUND, description: "Not Found", content: new OA\JsonContent(ref: new Model(type: ErrorDto::class)))]
    #[OA\RequestBody(description: "In order to update the product, you need to provide all the required data.", content: new OA\JsonContent(ref: new Model(type: ProductDto::class)))]
    #[OA\Response(
        response: Response::HTTP_OK, description: 'Successful response with the newly updated product.',
        content: new OA\JsonContent(ref: new Model(type: UpdateProductDto::class))
    )]
    #[Route('/{id}/update', name: 'update_product', methods: ['PUT'], format: 'json')]
    public function updateProduct(
        #[MapRequestPayload] ProductDto $productDto,
        CategoryRepository $categoryRepository,
        Request $request,
        ProductRepository $productRepo, 
        ?Product $product
    ): Response
    {
        // Authorization check: Extracts the token from the Authorization header and verifies its validity
        if (!$this->tokenService->isValidToken($request)) {
            return $this->json([
                'status'=> 'error',
                'message' => 'Invalid token',
            ], Response::HTTP_UNAUTHORIZED);
        }

        /**
         * TODO: Create some custom validator for error messages, here we are 
         * using the default Asserts in product DTO, but the messages 
         * contain the full content.
         **/ 

        // Update the product
        try {
            $updatedProduct = $productRepo->updateProduct($product, $productDto, $categoryRepository);
            
            return $this->json([
                'status'=> 'success',
                'message' => 'Product updated successfully',
                'data' => $updatedProduct,
            ], Response::HTTP_OK, context: [
                AbstractNormalizer::GROUPS => ['get_products'],
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'status'=> 'error',
                'message' => 'An error occurred while processing your request',
                'error' => $e->getMessage(),
            ], $e->getCode() ?: 500);
        }
    }

    // ============================================================================================================================ DELETE
    /**
     * Deletes a product by its ID.
     *
     * This endpoint requires a valid authentication token in the request header.
     * Returns a success message if the product is deleted, or an error message if the product is not found.
     *
     * @param Request $request HTTP request object containing the authorization token
     * @param ProductRepository $productRepo Repository to handle product operations
     * @param Product|null $product The product entity to be deleted, or null if not found
     *
     * @return Response JSON response with a message indicating the result of the deletion operation.
     *         Success: {"message": "Product deleted successfully"}
     *         Failure: {"message": "Product not found"} or {"message": "Invalid token"} (for authorization errors)
     */
    #[OA\Response(response: Response::HTTP_INTERNAL_SERVER_ERROR, description: "Server Error", content: new OA\JsonContent(ref: new Model(type: ErrorDto::class)))]
    #[OA\Response(response: Response::HTTP_UNAUTHORIZED, description: "Unauthorized", content: new OA\JsonContent(ref: new Model(type: ErrorDto::class)))]
    #[OA\Response(response: Response::HTTP_NOT_FOUND, description: "Not Found", content: new OA\JsonContent(ref: new Model(type: ErrorDto::class)))]
    #[OA\Response(
        response: Response::HTTP_OK, description: 'Successful response with the given category deleted.',
        content: new OA\JsonContent(ref: new Model(type: DeleteDto::class))
    )]
    #[Route('/{product}/delete', name: 'delete_product', methods: ['DELETE'], format: 'json')]
    public function deleteProduct(
        Request $request,
        ProductRepository $productRepo, 
        ?Product $product
    ): Response
    {
        // Authorization check: Extracts the token from the Authorization header and verifies its validity
        if (!$this->tokenService->isValidToken($request)) {
            return $this->json([
                'status'=> 'error',
                'message' => 'Invalid token',
            ], Response::HTTP_UNAUTHORIZED);
        }

        /**
         * TODO: Create to check if a given user with this token can delete
         * this product
         **/ 

        // Attempt to delete the product
        $isDeleted = $productRepo->deleteProduct($product);

        // Return response based on the deletion result
        return $this->json([
            'message' => $isDeleted ? 'Product deleted successfully' : 'Product not found',
        ], $isDeleted ? Response::HTTP_OK : Response::HTTP_NOT_FOUND);
    }
}
