<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Attributes as OA;

#[OA\Schema]
class ProductDto
{
    public function __construct(
        #[OA\Property(type: 'string', example: 'Product name')]
        #[Assert\NotBlank(message: 'Please provide a name for the category')]
        public readonly ?string $name,

        #[OA\Property(type: 'string', example: 'Product description')]
        #[Assert\NotBlank(message: 'Please provide a description for the category')]
        public readonly ?string $description = '',
        
        #[OA\Property(type: 'float', example: '0.75')]
        #[Assert\NotBlank(message: 'Please provide a product price')]
        public readonly ?float $price = 0.0,
        
        #[OA\Property(type: 'int', example: '2')]
        #[Assert\NotBlank(message: 'Please provide a category')]
        public readonly ?int $categoryId = null,
    ) {

    }
}