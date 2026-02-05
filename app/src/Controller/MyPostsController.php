<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class MyPostsController extends AbstractController
{
    #[Route('/my-posts', name: 'app_my_posts')]
    #[IsGranted('ROLE_USER')]
    public function index(): Response
    {
        return $this->render('post/my_posts.html.twig');
    }
}
