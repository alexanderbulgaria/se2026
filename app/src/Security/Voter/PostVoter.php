<?php

namespace App\Security\Voter;

use App\Entity\Post;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class PostVoter extends Voter
{
    public const VIEW = 'POST_VIEW';
    public const EDIT = 'POST_EDIT';
    public const DELETE = 'POST_DELETE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return \in_array($attribute, [self::VIEW, self::EDIT, self::DELETE], true)
            && $subject instanceof Post;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        /** @var Post $post */
        $post = $subject;

        if (\in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return true;
        }

        $isAuthor = $post->getAuthor()?->getId() === $user->getId();

        $isCoAuthor = $post->getCoAuthors()->exists(
            fn (int $key, User $u) => $u->getId() === $user->getId()
        );

        return match ($attribute) {
            self::VIEW => $isAuthor || $isCoAuthor,
            self::EDIT => $isAuthor || $isCoAuthor,
            self::DELETE => $isAuthor,
            default => false,
        };
    }
}
