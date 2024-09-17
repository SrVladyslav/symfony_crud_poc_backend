<?php

namespace App\Dto\Responses;

use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Annotation\Model;

#[OA\Schema]
class DeleteDto
{
    public function __construct(
        #[OA\Property(type: 'string', example: 'success')]
        public readonly ?string $status,

        #[OA\Property(type: 'string', example: 'Category deleted successfully')]
        public readonly ?string $message
    ) {}
}
