<?php

namespace App\Entity;

use App\Repository\PostRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PostRepository::class)]
#[ORM\Table(name: "posts")]
class Post
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\Type('string')]
    #[Assert\Length(
        max: 100,
        maxMessage: 'Tytuł jest za długi'
    )]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\Type('string')]
    #[Assert\Length(
        max: 3000,
        maxMessage: 'Zawartość wpisu zbyt długa'
    )]
    private ?string $content = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updated_at = null;

    #[ORM\ManyToOne(inversedBy: 'posts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'likedPosts')]
    #[ORM\JoinTable(name:'likes')]

    private Collection $usersThatLiked;

    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'dislikedPosts')]
    #[ORM\JoinTable(name:'dislikes')]
    private Collection $usersThatDontLike;

    public function __construct()
    {
        $this->usersThatLiked = new ArrayCollection();
        $this->usersThatDontLike = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updated_at): static
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsersThatLiked(): Collection
    {
        return $this->usersThatLiked;
    }

    public function addUsersThatLiked(User $usersThatLiked): static
    {
        if (!$this->usersThatLiked->contains($usersThatLiked)) {
            $this->usersThatLiked->add($usersThatLiked);
            $usersThatLiked->addLikedPost($this);
        }

        return $this;
    }

    public function removeUsersThatLiked(User $usersThatLiked): static
    {
        if ($this->usersThatLiked->removeElement($usersThatLiked)) {
            $usersThatLiked->removeLikedPost($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsersThatDontLike(): Collection
    {
        return $this->usersThatDontLike;
    }

    public function addUsersThatDontLike(User $usersThatDontLike): static
    {
        if (!$this->usersThatDontLike->contains($usersThatDontLike)) {
            $this->usersThatDontLike->add($usersThatDontLike);
            $usersThatDontLike->addDislikedPost($this);
        }

        return $this;
    }

    public function removeUsersThatDontLike(User $usersThatDontLike): static
    {
        if ($this->usersThatDontLike->removeElement($usersThatDontLike)) {
            $usersThatDontLike->removeDislikedPost($this);
        }

        return $this;
    }
}
