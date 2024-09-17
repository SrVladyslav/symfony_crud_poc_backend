<?php

namespace App\Dto\Responses;

use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Annotation\Model;

#[OA\Schema]
class ErrorDto
{
    public function __construct(
        #[OA\Property(type: 'string', example: 'error')]
        public readonly ?string $status,

        #[OA\Property(type: 'string', example: 'Invalid token / Category not found')]
        public readonly ?string $message,
    ) {

    }
}