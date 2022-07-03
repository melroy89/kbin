<?php declare(strict_types=1);

namespace App\Entity;

use App\Entity\Contracts\ContentInterface;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;

#[Entity]
class MagazineLogEntryCommentDeleted extends MagazineLog
{
    #[ManyToOne(targetEntity: EntryComment::class)]
    #[JoinColumn(nullable: true, onDelete: 'SET NULL')]
    public ?EntryComment $entryComment;

    public function __construct(EntryComment $comment, User $user)
    {
        parent::__construct($comment->magazine, $user);

        $this->entryComment = $comment;
    }

    public function getType(): string
    {
        return 'log_entry_comment_deleted';
    }

    public function getComment(): EntryComment
    {
        return $this->entryComment;
    }

    public function getSubject(): ContentInterface
    {
        return $this->entryComment;
    }

    public function clearSubject(): MagazineLog
    {
        $this->entryComment = null;

        return $this;
    }
}
