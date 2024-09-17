<?php

namespace App\Dto;

use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

class CategoryDto
{
    public function __construct(
        #[OA\Property(type: 'string', maxLength: 128, nullable: false, example: 'Category name')]
        #[Assert\NotBlank(message: 'Please provide a name for the category')]
        public readonly ?string $name,

        #[OA\Property(type: 'string', maxLength: 255, default: '', nullable: false, example: 'Some category description')]
        #[Assert\NotBlank(message: 'Please provide a description for the category')]
        public readonly ?string $description = ''
    ) {

    }
}