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
    new UniqueConstraint(name: 'user_post_vote_idx', columns: ['user_id', 'post_id'])
])]
#[AssociationOverrides([
    new AssociationOverride(name: 'user', inversedBy: 'postVotes')
])]
#[Cache('NONSTRICT_READ_WRITE')]
class PostVote extends Vote
{
    #[ManyToOne(targetEntity: Post::class, inversedBy: 'votes')]
    #[JoinColumn(name: 'post_id', nullable: true, onDelete: 'cascade')]
    public ?Post $post;

    public function __construct(int $choice, User $user, ?Post $post)
    {
        parent::__construct($choice, $user, $post->user);

        $this->post = $post;
    }
}
