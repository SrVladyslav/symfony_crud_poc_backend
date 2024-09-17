<?php

namespace App\Dto\Responses;

use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Annotation\Model;

#[OA\Schema]
class GetProductDto
{
    public function __construct(
        #[OA\Property(type: 'string', example: 'success')]
        public readonly ?string $status,

        #[OA\Property(type: 'string', example: 'Found successfully')]
        public readonly ?string $message,

        #[OA\Property(
            type: 'object',
            properties: [
                new OA\Property(property: 'id', type: 'integer', example: 19),
                new OA\Property(property: 'name', type: 'string', example: 'Product 10'),
                new OA\Property(property: 'description', type: 'string', example: 'Product description'),
                new OA\Property(property: 'price', type: 'number', format: 'float', example: 4.99),
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
        )]
        public readonly ?array $data
    ){

    }
}