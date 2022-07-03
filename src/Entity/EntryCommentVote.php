<?php declare(strict_types = 1);

namespace App\Entity;

use Doctrine\ORM\Mapping\AssociationOverride;
use Doctrine\ORM\Mapping\AssociationOverrides;
use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\UniqueConstraint;

#[Entity]
#[Table(uniqueConstraints: [
    new UniqueConstraint(name: 'user_entry_comment_vote_idx', columns: ['user_id', 'comment_id'])
])]
#[AssociationOverrides([
    new AssociationOverride(name: 'user', inversedBy: 'entryCommentVotes')
])]
#[Cache('NONSTRICT_READ_WRITE')]
class EntryCommentVote extends Vote
{
    #[ManyToOne(targetEntity: EntryComment::class,inversedBy: 'votes')]
    #[JoinColumn(name: 'comment_id', nullable: true, onDelete: 'cascade')]
    public ?EntryComment $comment;

    public function __construct(int $choice, User $user, EntryComment $comment)
    {
        parent::__construct($choice, $user, $comment->user);

        $this->comment = $comment;
    }

    public function getComment(): EntryComment
    {
        return $this->comment;
    }

    public function setComment(?EntryComment $comment): self
    {
        $this->comment = $comment;

        return $this;
    }
}
