<?php declare(strict_types=1);

namespace App\Entity;

use App\Entity\Contracts\FavouriteInterface;
use App\Entity\Contracts\ReportInterface;
use App\Entity\Contracts\VisibilityInterface;
use App\Entity\Contracts\VoteInterface;
use App\Entity\Traits\CreatedAtTrait;
use App\Entity\Traits\EditedAtTrait;
use App\Entity\Traits\VisibilityTrait;
use App\Entity\Traits\VotableTrait;
use App\Repository\PostCommentRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OrderBy;
use Traversable;
use Webmozart\Assert\Assert;

#[Entity(repositoryClass: PostCommentRepository::class)]
class PostComment implements VoteInterface, VisibilityInterface, ReportInterface, FavouriteInterface
{
    use VotableTrait;
    use VisibilityTrait;
    use CreatedAtTrait {
        CreatedAtTrait::__construct as createdAtTraitConstruct;
    }
    use EditedAtTrait;

    #[Id]
    #[GeneratedValue]
    #[Column(type: 'integer')]
    private int $id;

    #[ManyToOne(targetEntity: User::class, inversedBy: 'postComments')]
    #[JoinColumn(nullable: false)]
    public User $user;

    #[ManyToOne(targetEntity: Post::class, inversedBy: 'comments')]
    #[JoinColumn(nullable: false, onDelete: 'cascade')]
    public ?Post $post;

    #[ManyToOne(targetEntity: Magazine::class)]
    #[JoinColumn(nullable: false, onDelete: 'cascade')]
    public ?Magazine $magazine;

    #[ManyToOne(targetEntity: PostComment::class, inversedBy: 'children')]
    #[JoinColumn(nullable: true, onDelete: 'cascade')]
    public ?PostComment $parent;

    #[ManyToOne(targetEntity: Image::class, cascade: ['persist'])]
    #[JoinColumn(nullable: true)]
    public ?Image $image = null;

    #[Column(type: 'text', length: 4500, nullable: true)]
    public ?string $body;

    #[Column(type: 'integer', nullable: false)]
    public int $favouriteCount = 0;

    #[Column(type: 'datetimetz', nullable: true)]
    public ?DateTime $lastActive = null;

    #[Column(type: 'string', nullable: true)]
    public ?string $ip = null;

    #[Column(type: 'array', nullable: true)]
    public ?array $tags = null;

    #[OneToMany(mappedBy: 'parent', targetEntity: PostComment::class, orphanRemoval: true)]
    #[OrderBy(['createdAt' => 'ASC'])]
    public Collection $children;

    #[OneToMany(mappedBy: 'comment', targetEntity: PostCommentVote::class, cascade: ['persist'], fetch: 'EXTRA_LAZY', orphanRemoval: true)]
    public Collection $votes;

    #[OneToMany(mappedBy: 'postComment', targetEntity: PostCommentReport::class, cascade: ['remove'], fetch: 'EXTRA_LAZY', orphanRemoval: true)]
    public Collection $reports;

    #[OneToMany(mappedBy: 'postComment', targetEntity: PostCommentFavourite::class, cascade: ['remove'], fetch: 'EXTRA_LAZY', orphanRemoval: true)]
    public Collection $favourites;

    #[OneToMany(mappedBy: 'postComment', targetEntity: PostCommentCreatedNotification::class, cascade: ['remove'], fetch: 'EXTRA_LAZY', orphanRemoval: true)]
    public Collection $notifications;

    public function __construct(string $body, ?Post $post, User $user, ?PostComment $parent = null, ?string $ip = null)
    {
        $this->body       = $body;
        $this->post       = $post;
        $this->user       = $user;
        $this->parent     = $parent;
        $this->ip         = $ip;
        $this->votes      = new ArrayCollection();
        $this->children   = new ArrayCollection();
        $this->reports    = new ArrayCollection();
        $this->favourites = new ArrayCollection();

        $this->createdAtTraitConstruct();
        $this->updateLastActive();
    }

    public function updateLastActive(): void
    {
        $this->lastActive = DateTime::createFromImmutable($this->createdAt);

        $this->post->lastActive = DateTime::createFromImmutable($this->createdAt);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function addVote(Vote $vote): self
    {
        Assert::isInstanceOf($vote, PostCommentVote::class);

        if (!$this->votes->contains($vote)) {
            $this->votes->add($vote);
            $vote->setComment($this);
        }

        return $this;
    }

    public function removeVote(Vote $vote): self
    {
        Assert::isInstanceOf($vote, PostCommentVote::class);

        if ($this->votes->removeElement($vote)) {
            // set the owning side to null (unless already changed)
            if ($vote->getComment() === $this) {
                $vote->setComment(null);
            }
        }

        return $this;
    }

    public function getChildrenRecursive(int &$startIndex = 0): Traversable
    {
        foreach ($this->children as $child) {
            yield $startIndex++ => $child;
            yield from $child->getChildrenRecursive($startIndex);
        }
    }

    public function softDelete(): void
    {
        $this->visibility = self::VISIBILITY_SOFT_DELETED;
    }

    public function trash(): void
    {
        $this->visibility = self::VISIBILITY_TRASHED;
    }

    public function restore(): void
    {
        $this->visibility = VisibilityInterface::VISIBILITY_VISIBLE;
    }

    public function isAuthor(User $user): bool
    {
        return $user === $this->user;
    }

    public function getShortTitle(): string
    {
        $body = $this->body;
        preg_match('/^(.*)$/m', $body, $firstLine);
        $firstLine = $firstLine[0];

        if (grapheme_strlen($firstLine) <= 60) {
            return $firstLine;
        }

        return grapheme_substr($firstLine, 0, 60).'…';
    }

    public function getMagazine(): ?Magazine
    {
        return $this->magazine;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function updateCounts(): self
    {
        $this->favouriteCount = $this->favourites->count();

        return $this;
    }

    public function isFavored(User $user): bool
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('user', $user));

        return $this->favourites->matching($criteria)->count() > 0;
    }

    public function __sleep()
    {
        return [];
    }
}
