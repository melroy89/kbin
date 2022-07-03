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
    new UniqueConstraint(name: 'user_post_comment_vote_idx', columns: ['user_id', 'comment_id'])
])]
#[AssociationOverrides([
    new AssociationOverride(name: 'user', inversedBy: 'postCommentVotes')
])]
#[Cache('NONSTRICT_READ_WRITE')]
class PostCommentVote extends Vote
{
    #[ManyToOne(targetEntity: PostComment::class,inversedBy: 'votes')]
    #[JoinColumn(name: 'comment_id', nullable: true, onDelete: 'cascade')]
    public ?PostComment $comment;

    public function __construct(int $choice, User $user, PostComment $comment)
    {
        parent::__construct($choice, $user, $comment->user);

        $this->comment = $comment;
    }

    public function getComment(): PostComment
    {
        return $this->comment;
    }

    public function setComment(?PostComment $comment): self
    {
        $this->comment = $comment;

        return $this;
    }
}
