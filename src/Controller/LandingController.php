<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


/**
 * Redirects the user to the Swagger UI page.
 */
class LandingController extends AbstractController
{
    #[Route('/', name: 'landing')]
    public function index(): Response
    {
        return $this->redirectToRoute('swagger_ui');
    }
}
