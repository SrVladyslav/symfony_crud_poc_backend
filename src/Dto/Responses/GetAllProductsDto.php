<?php

namespace App\Dto\Responses;

use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Annotation\Model;

#[OA\Schema]
class GetAllProductsDto {
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

        #[OA\Property(type: 'string', example: '/api/products/get?page=x-1')]
        public readonly ?string $prevPage,

        #[OA\Property(type: 'string', example: '/api/products/get?page=x+1')]
        public readonly ?string $nextPage,

        #[OA\Property(
            type: 'array',
            items: new OA\Items(
                type: 'object',
                properties: [
                    new OA\Property(property: 'id', type: 'integer', example: 19),
                    new OA\Property(property: 'name', type: 'string', example: 'Product 19'),
                    new OA\Property(property: 'description', type: 'string', example: 'Product description 19'),
                    new OA\Property(property: 'price', type: 'number', format: 'float', example: 0.75),
                    new OA\Property(
                        property: 'category',
                        type: 'object',
                        properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 32),
                            new OA\Property(property: 'name', type: 'string', example: 'Category 1'),
                            new OA\Property(property: 'description', type: 'string', example: 'Description for category 1.')
                        ]
                    )
                ]
            )
        )]
        public readonly ?array $data
    ){

    }
}
