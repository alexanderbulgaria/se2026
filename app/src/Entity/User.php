<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\OneToMany(mappedBy: 'author', targetEntity: Post::class)]
    private Collection $posts;

    #[ORM\ManyToMany(targetEntity: Post::class, mappedBy: 'coAuthors')]
    private Collection $coauthoredPosts;

    public function __construct()
    {
        $this->posts = new ArrayCollection();
        $this->coauthoredPosts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // гарантираме, че всеки има поне ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // Ако съхраняваш временни чувствителни данни, изчисти ги тук
        // $this->plainPassword = null;
    }

    /**
     * @return Collection<int, Post>
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function addPost(Post $post): static
    {
        if (!$this->posts->contains($post)) {
            $this->posts->add($post);
            $post->setAuthor($this);
        }

        return $this;
    }

    public function removePost(Post $post): static
    {
        // Забележка: Post::author е owning side и е NOT NULL.
        // Премахване от тази колекция само по себе си НЕ премахва връзката в базата.
        // За реално „махане“ трябва да изтриеш Post или да смениш автора.
        $this->posts->removeElement($post);

        return $this;
    }

    /**
     * @return Collection<int, Post>
     */
    public function getCoauthoredPosts(): Collection
    {
        return $this->coauthoredPosts;
    }

    public function addCoauthoredPost(Post $post): static
    {
        if (!$this->coauthoredPosts->contains($post)) {
            $this->coauthoredPosts->add($post);

            // За да е синхронизирано двупосочно, Post трябва да има addCoAuthor().
            if (method_exists($post, 'addCoAuthor')) {
                $post->addCoAuthor($this);
            }
        }

        return $this;
    }

    public function removeCoauthoredPost(Post $post): static
    {
        if ($this->coauthoredPosts->removeElement($post)) {
            // За да е синхронизирано двупосочно, Post трябва да има removeCoAuthor().
            if (method_exists($post, 'removeCoAuthor')) {
                $post->removeCoAuthor($this);
            }
        }

        return $this;
    }
}
