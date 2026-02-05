<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class PublicationsController extends AbstractController
{
    #[Route('/publications', name: 'app_publications')]
    public function index(): Response
    {
        return $this->render('publications/index.html.twig');
    }

    #[Route('/my-posts', name: 'app_my_posts')]
    #[IsGranted('ROLE_USER')]
    public function myPosts(): Response
    {
        return $this->render('publications/my_posts.html.twig');
    }
}
