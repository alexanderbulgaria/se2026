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

        $posts = $postRepository
            ->createVisibleToUserQueryBuilder($user)
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

        $form = $this->createForm(PostType::class, $post, [
            'can_manage_coauthors' => true,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $now = new \DateTimeImmutable();
            if ($post->getCreatedAt() === null) {
                $post->setCreatedAt($now);
            }
            $post->setUpdatedAt($now);

            $this->ensureAuthorNotInCoAuthors($post);

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

        $isAuthor = $post->getAuthor()?->getId() === $user->getId();

        $form = $this->createForm(PostType::class, $post, [
            'can_manage_coauthors' => $isAuthor,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $post->setUpdatedAt(new \DateTimeImmutable());

            // Основният автор не трябва да фигурира като съавтор
            $this->ensureAuthorNotInCoAuthors($post);

            // Ако редактира съавтор (не основният автор), гарантираме, че остава съавтор
            if (!$isAuthor) {
                $this->ensureUserIsCoAuthor($post, $user);
            }

            $entityManager->flush();

            $this->addFlash('success', 'Промените са запазени.');

            return $this->redirectToRoute('app_post_show', ['id' => $post->getId()], Response::HTTP_SEE_OTHER);
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

    private function ensureAuthorNotInCoAuthors(Post $post): void
    {
        $author = $post->getAuthor();
        if ($author === null) {
            return;
        }

        foreach ($post->getCoAuthors() as $coAuthor) {
            if ($coAuthor->getId() !== null && $coAuthor->getId() === $author->getId()) {
                $post->removeCoAuthor($coAuthor);
                break;
            }
        }
    }

    private function ensureUserIsCoAuthor(Post $post, User $user): void
    {
        $exists = $post->getCoAuthors()->exists(
            fn (int $key, User $u) => $u->getId() === $user->getId()
        );

        if (!$exists) {
            $post->addCoAuthor($user);
        }
    }
}
