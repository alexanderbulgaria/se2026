<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\User;
use App\Form\PostType;
use App\Repository\PostRepository;
use App\Security\Voter\PostVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/post')]
class PostController extends AbstractController
{
    #[IsGranted('ROLE_USER')]
    #[Route('/', name: 'app_post_index', methods: ['GET'])]
    public function index(PostRepository $postRepository): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        // Показваме само публикациите, които са видими за текущия потребител:
        // основен автор ИЛИ съавтор.
        $posts = $postRepository->createQueryBuilder('p')
            ->leftJoin('p.coAuthors', 'ca')
            ->andWhere('p.author = :u OR ca = :u')
            ->setParameter('u', $user)
            ->orderBy('p.createdAt', 'DESC')
            ->distinct()
            ->getQuery()
            ->getResult();

        return $this->render('post/index.html.twig', [
            'posts' => $posts,
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/new', name: 'app_post_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $post = new Post();
        $post->setAuthor($user);

        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Не допускаме основният автор да е добавен и като съавтор
            $this->preventSelfCoAuthor($post, $user);

            $entityManager->persist($post);
            $entityManager->flush();

            $this->addFlash('success', 'Публикацията е създадена успешно.');

            return $this->redirectToRoute('app_my_posts', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('post/new.html.twig', [
            'post' => $post,
            'form' => $form,
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/{id}', name: 'app_post_show', methods: ['GET'])]
    public function show(Post $post): Response
    {
        $this->denyAccessUnlessGranted(PostVoter::VIEW, $post);

        return $this->render('post/show.html.twig', [
            'post' => $post,
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/{id}/edit', name: 'app_post_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Post $post, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted(PostVoter::EDIT, $post);

        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Не допускаме основният автор да е добавен и като съавтор
            $this->preventSelfCoAuthor($post, $user);

            $entityManager->flush();

            $this->addFlash('success', 'Промените са запазени.');

            return $this->redirectToRoute('app_my_posts', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('post/edit.html.twig', [
            'post' => $post,
            'form' => $form,
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/{id}', name: 'app_post_delete', methods: ['POST'])]
    public function delete(Request $request, Post $post, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted(PostVoter::DELETE, $post);

        if (!$this->isCsrfTokenValid('delete' . $post->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Невалиден токен. Опитайте отново.');
            return $this->redirectToRoute('app_my_posts', [], Response::HTTP_SEE_OTHER);
        }

        $entityManager->remove($post);
        $entityManager->flush();

        $this->addFlash('success', 'Публикацията е изтрита.');

        return $this->redirectToRoute('app_my_posts', [], Response::HTTP_SEE_OTHER);
    }

    private function preventSelfCoAuthor(Post $post, User $user): void
    {
        foreach ($post->getCoAuthors() as $coAuthor) {
            if ($coAuthor->getId() !== null && $coAuthor->getId() === $user->getId()) {
                $post->removeCoAuthor($coAuthor);
                break;
            }
        }
    }
}
