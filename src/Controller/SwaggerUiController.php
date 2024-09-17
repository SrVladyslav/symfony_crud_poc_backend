<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SwaggerUiController extends AbstractController
{
    #[Route('/api-docs', name: 'swagger_ui')]
    public function index(): Response
    {
        return $this->render('swagger_ui.html.twig');
    }
}
