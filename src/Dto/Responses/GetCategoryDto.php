<?php

namespace App\Dto\Responses;

use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Annotation\Model;

#[OA\Schema]
class FullProductDto
{
    public function __construct(
        #[OA\Property(type: 'integer', example: 8)]
        public readonly ?int $id,

        #[OA\Property(type: 'string', example: 'Product name')]
        public readonly ?string $name,

        #[OA\Property(type: 'string', example: 'Product description')]
        public readonly ?string $description,

        #[OA\Property(type: 'number', format: 'float', example: 9.99)]
        public readonly ?float $price
    ) {}
}

#[OA\Schema]
class FullCategoryDto
{
    public function __construct(
        #[OA\Property(type: 'integer', example: 28)]
        public readonly ?int $id,

        #[OA\Property(type: 'string', example: 'Category name')]
        public readonly ?string $name,

        #[OA\Property(type: 'string', example: 'Category description')]
        public readonly ?string $description,

        #[OA\Property(
            type: 'array',
            items: new OA\Items(ref: new Model(type: FullProductDto::class))
        )]
        public readonly ?array $products
    ) {}
}

#[OA\Schema]
class GetCategoryDto
{
    public function __construct(
        #[OA\Property(type: 'string', example: 'success')]
        public readonly ?string $status,

        #[OA\Property(type: 'string', example: 'Found successfully')]
        public readonly ?string $message,

        #[OA\Property(
            type: 'object',
            properties: [
                new OA\Property(property: 'id', type: 'integer', example: 28),
                new OA\Property(property: 'name', type: 'string', example: 'Category name'),
                new OA\Property(property: 'description', type: 'string', example: 'Category description'),
                new OA\Property(
                    property: 'products',
                    type: 'array',
                    items: new OA\Items(ref: new Model(type: FullProductDto::class))
                )
            ]
        )]
        public readonly ?FullCategoryDto $data
    ) {}
}
