<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class PublicationsController extends AbstractController
{
    #[Route('/publications', name: 'app_publications', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('publications/index.html.twig');
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/my-posts', name: 'app_my_posts', methods: ['GET'])]
    public function myPosts(PostRepository $postRepository): Response
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
