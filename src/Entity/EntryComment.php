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
use App\Repository\EntryCommentRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
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

#[Entity(repositoryClass: EntryCommentRepository::class)]
class EntryComment implements VoteInterface, VisibilityInterface, ReportInterface, FavouriteInterface
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

    #[ManyToOne(targetEntity: User::class, inversedBy: 'entryComments')]
    #[JoinColumn(nullable: false)]
    public User $user;

    #[ManyToOne(targetEntity: Entry::class, inversedBy: 'comments')]
    #[JoinColumn(nullable: false)]
    public ?Entry $entry;

    #[ManyToOne(targetEntity: Magazine::class)]
    #[JoinColumn(nullable: false, onDelete: 'cascade')]
    public ?Magazine $magazine;

    #[ManyToOne(targetEntity: Image::class, cascade: ['persist'])]
    #[JoinColumn(nullable: true)]
    public ?Image $image = null;

    #[ManyToOne(targetEntity: EntryComment::class, inversedBy: 'children')]
    #[JoinColumn(nullable: true, onDelete: 'cascade')]
    public ?EntryComment $parent = null;

    #[ManyToOne(targetEntity: EntryComment::class)]
    #[JoinColumn(nullable: true)]
    public ?EntryComment $root = null;

    #[Column(type: 'text', length: 4500, nullable: true)]
    public ?string $body = null;

    #[Column(type: 'integer', nullable: false)]
    public int $favouriteCount = 0;

    #[Column(type: 'datetimetz', nullable: false)]
    public ?DateTime $lastActive = null;

    #[Column(type: 'string', nullable: true)]
    public ?string $ip = null;

    #[Column(type: 'array', nullable: true)]
    public ?array $tags = null;

    #[OneToMany(mappedBy: 'parent', targetEntity: EntryComment::class, orphanRemoval: true)]
    #[OrderBy(['createdAt' => 'ASC'])]
    public Collection $children;

    #[OneToMany(mappedBy: 'comment', targetEntity: EntryCommentVote::class, cascade: ['persist'], fetch: 'EXTRA_LAZY', orphanRemoval: true)]
    public Collection $votes;

    #[OneToMany(mappedBy: 'entryComment', targetEntity: EntryCommentReport::class, cascade: ['remove'], fetch: 'EXTRA_LAZY', orphanRemoval: true)]
    public Collection $reports;

    #[OneToMany(mappedBy: 'entryComment', targetEntity: EntryCommentFavourite::class, cascade: ['remove'], fetch: 'EXTRA_LAZY', orphanRemoval: true)]
    public Collection $favourites;

    #[OneToMany(mappedBy: 'entryComment', targetEntity: EntryCommentCreatedNotification::class, cascade: ['remove'], fetch: 'EXTRA_LAZY', orphanRemoval: true)]
    public Collection $notifications;

    public function __construct(string $body, ?Entry $entry, User $user, ?EntryComment $parent = null, ?string $ip = null)
    {
        $this->body          = $body;
        $this->entry         = $entry;
        $this->user          = $user;
        $this->parent        = $parent;
        $this->ip            = $ip;
        $this->votes         = new ArrayCollection();
        $this->children      = new ArrayCollection();
        $this->reports       = new ArrayCollection();
        $this->favourites    = new ArrayCollection();
        $this->notifications = new ArrayCollection();

        if ($parent) {
            $this->root = $parent->root ?? $parent;
        }

        $this->createdAtTraitConstruct();
        $this->updateLastActive();
    }

    public function updateLastActive(): void
    {
        $this->lastActive = DateTime::createFromImmutable($this->createdAt);

        if (!$this->root) {

            return;
        }

        $this->root->lastActive = DateTime::createFromImmutable($this->createdAt);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function addVote(Vote $vote): self
    {
        Assert::isInstanceOf($vote, EntryCommentVote::class);

        if (!$this->votes->contains($vote)) {
            $this->votes->add($vote);
            $vote->setComment($this);
        }

        return $this;
    }

    public function removeVote(Vote $vote): self
    {
        Assert::isInstanceOf($vote, EntryCommentVote::class);

        if ($this->votes->removeElement($vote)) {
            // set the owning side to null (unless already changed)
            if ($vote->comment === $this) {
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
