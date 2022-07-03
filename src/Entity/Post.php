<?php declare(strict_types=1);

namespace App\Entity;

use App\Entity\Contracts\CommentInterface;
use App\Entity\Contracts\FavouriteInterface;
use App\Entity\Contracts\RankingInterface;
use App\Entity\Contracts\ReportInterface;
use App\Entity\Contracts\VisibilityInterface;
use App\Entity\Contracts\VoteInterface;
use App\Entity\Traits\CreatedAtTrait;
use App\Entity\Traits\EditedAtTrait;
use App\Entity\Traits\RankingTrait;
use App\Entity\Traits\VisibilityTrait;
use App\Entity\Traits\VotableTrait;
use App\Repository\PostRepository;
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
use Webmozart\Assert\Assert;

#[Entity(repositoryClass: PostRepository::class)]
class Post implements VoteInterface, CommentInterface, VisibilityInterface, RankingInterface, ReportInterface, FavouriteInterface
{
    use VotableTrait;
    use RankingTrait;
    use VisibilityTrait;
    use CreatedAtTrait {
        CreatedAtTrait::__construct as createdAtTraitConstruct;
    }
    use EditedAtTrait;

    #[Id]
    #[GeneratedValue]
    #[Column(type: 'integer')]
    private int $id;

    #[ManyToOne(targetEntity: User::class, inversedBy: 'posts')]
    #[JoinColumn(nullable: false)]
    public User $user;

    #[ManyToOne(targetEntity: Magazine::class, inversedBy: 'posts')]
    #[JoinColumn(nullable: false, onDelete: 'cascade')]
    public ?Magazine $magazine;

    #[ManyToOne(targetEntity: Image::class, cascade: ['persist'])]
    #[JoinColumn(nullable: false)]
    public ?Image $image = null;

    #[Column(type: 'string', length: 255, nullable: false)]
    public string $slug;

    #[Column(type: 'text', length: 15000, nullable: false)]
    public ?string $body = null;

    #[Column(type: 'integer', nullable: false)]
    public int $commentCount = 0;

    #[Column(type: 'integer', nullable: false)]
    public int $favouriteCount = 0;

    #[Column(type: 'integer', nullable: false)]
    public int $score = 0;

    #[Column(type: 'boolean', nullable: false)]
    public ?bool $isAdult = false;

    #[Column(type: 'datetimetz', nullable: true)]
    public ?DateTime $lastActive = null;

    #[Column(type: 'string', nullable: true)]
    public ?string $ip = null;

    #[Column(type: 'array', nullable: true)]
    public ?array $tags = null;

    #[OneToMany(mappedBy: 'post', targetEntity: PostComment::class, orphanRemoval: true)]
    public Collection $comments;

    #[OneToMany(mappedBy: 'post', targetEntity: PostVote::class, cascade: ['persist'], fetch: 'EXTRA_LAZY', orphanRemoval: true)]
    public Collection $votes;

    #[OneToMany(mappedBy: 'post', targetEntity: PostReport::class, cascade: ['remove'], fetch: 'EXTRA_LAZY', orphanRemoval: true)]
    public Collection $reports;

    #[OneToMany(mappedBy: 'post', targetEntity: PostFavourite::class, cascade: ['remove'], fetch: 'EXTRA_LAZY', orphanRemoval: true)]
    public Collection $favourites;

    #[OneToMany(mappedBy: 'post', targetEntity: PostCreatedNotification::class, cascade: ['remove'], fetch: 'EXTRA_LAZY', orphanRemoval: true)]
    public Collection $notifications;

    public function __construct(string $body, Magazine $magazine, User $user, ?bool $isAdult = false, ?string $ip = null)
    {
        $this->body          = $body;
        $this->magazine      = $magazine;
        $this->user          = $user;
        $this->isAdult       = $isAdult ?? false;
        $this->ip            = $ip;
        $this->comments      = new ArrayCollection();
        $this->votes         = new ArrayCollection();
        $this->reports       = new ArrayCollection();
        $this->favourites    = new ArrayCollection();
        $this->notifications = new ArrayCollection();

        $user->addPost($this);

        $this->createdAtTraitConstruct();
        $this->updateLastActive();
    }

    public function updateLastActive(): void
    {
        $this->comments->get(-1);

        $criteria = Criteria::create()
            ->orderBy(['createdAt' => 'DESC'])
            ->setMaxResults(1);

        $lastComment = $this->comments->matching($criteria)->first();

        if ($lastComment) {
            $this->lastActive = DateTime::createFromImmutable($lastComment->createdAt);
        } else {
            $this->lastActive = DateTime::createFromImmutable($this->getCreatedAt());
        }
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getBestComments(): Collection
    {
        $criteria = Criteria::create()
            ->orderBy(['upVotes' => 'DESC', 'createdAt' => 'ASC']);

        $comments = $this->comments->matching($criteria);
        $comments = new ArrayCollection($comments->slice(0, 2));

        $iterator = $comments->getIterator();
        $iterator->uasort(function ($a, $b) {
            return ($a->createdAt < $b->createdAt) ? -1 : 1;
        });

        return new ArrayCollection(iterator_to_array($iterator));
    }

    public function getLastComments(): Collection
    {
        $criteria = Criteria::create()
            ->orderBy(['createdAt' => 'ASC']);

        $comments = $this->comments->matching($criteria);

        return new ArrayCollection($comments->slice(-2, 2));
    }

    public function addComment(PostComment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->post = $this;
        }

        $this->updateCounts();
        $this->updateRanking();
        $this->updateLastActive();

        return $this;
    }

    public function updateCounts(): self
    {
        $criteria = Criteria::create()
            ->andWhere(Criteria::expr()->eq('visibility', VisibilityInterface::VISIBILITY_VISIBLE));

        $this->commentCount   = $this->comments->matching($criteria)->count();
        $this->favouriteCount = $this->favourites->count();

        return $this;
    }

    public function removeComment(PostComment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            if ($comment->post === $this) {
                $comment->post = null;
            }
        }

        $this->updateCounts();
        $this->updateRanking();
        $this->updateLastActive();

        return $this;
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

    public function addVote(Vote $vote): self
    {
        Assert::isInstanceOf($vote, PostVote::class);

        if (!$this->votes->contains($vote)) {
            $this->votes->add($vote);
            $vote->post = $this;
        }

        $this->score = $this->getUpVotes()->count() - $this->getDownVotes()->count();
        $this->updateRanking();

        return $this;
    }

    public function removeVote(Vote $vote): self
    {
        Assert::isInstanceOf($vote, PostVote::class);

        if ($this->votes->removeElement($vote)) {
            if ($vote->getPost() === $this) {
                $vote->setPost(null);
            }
        }

        $this->score = $this->getUpVotes()->count() - $this->getDownVotes()->count();
        $this->updateRanking();

        return $this;
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

    public function getCommentCount(): int
    {
        return $this->commentCount;
    }

    public function getScore(): int
    {
        return $this->score;
    }

    public function getMagazine(): ?Magazine
    {
        return $this->magazine;
    }

    public function getUser(): ?User
    {
        return $this->user;
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
