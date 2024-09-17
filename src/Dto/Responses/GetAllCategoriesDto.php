<?php

namespace App\Dto\Responses;

// use OpenApi\Annotations as OA;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Annotation\Model;

#[OA\Schema]
class GetAllCategoriesDto {
    public function __construct(
        #[OA\Property(type: 'string', example: 'success')]
        public readonly ?string $status,

        #[OA\Property(type: 'string', example: 'Found successfully')]
        public readonly ?string $message,

        #[OA\Property(type: 'string', example: '1')]
        public readonly ?string $page,

        #[OA\Property(type: 'string', example: '10')]
        public readonly ?string $limit,

        #[OA\Property(type: 'string', example: '5')]
        public readonly ?string $totalPages,

        #[OA\Property(type: 'string', example: '/api/categories/get?page=x-1')]
        public readonly ?string $prevPage,

        #[OA\Property(type: 'string', example: '/api/categories/get?page=x+1')]
        public readonly ?string $nextPage,

        #[OA\Property(
            type: 'array',
            items: new OA\Items(
                type: 'object',
                properties: [
                    new OA\Property(property: 'id', type: 'integer', example: 1),
                    new OA\Property(property: 'name', type: 'string', example: 'Category name'),
                    new OA\Property(property: 'description', type: 'string', example: 'Category description'),
                    new OA\Property(
                        property: 'products',
                        type: 'array',
                        items: new OA\Items(
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 8),
                                new OA\Property(property: 'name', type: 'string', example: 'Product name'),
                                new OA\Property(property: 'description', type: 'string', example: 'Product description'),
                                new OA\Property(property: 'price', type: 'number', format: 'float', example: 9.99),
                                new OA\Property(property: 'category', type: 'integer', example: 1),
                            ]
                        )
                    )
                ]
            )
        )]
        public readonly ?array $data
    ){

    }
}