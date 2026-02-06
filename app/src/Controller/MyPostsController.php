<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MyPostsController extends AbstractController
{
    #[Route('/my-posts', name: 'app_my_posts', methods: ['GET'])]
    public function index(PostRepository $postRepository): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $posts = $postRepository->createQueryBuilder('p')
            ->andWhere('p.author = :u')
            ->setParameter('u', $user)
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        return $this->render('my_posts/index.html.twig', [
            'posts' => $posts,
        ]);
    }
}
