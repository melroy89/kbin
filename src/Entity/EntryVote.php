<?php declare(strict_types = 1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\AssociationOverride;
use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\UniqueConstraint;

#[Entity]
#[Table(uniqueConstraints: [
    new UniqueConstraint(name: 'user_entry_vote_idx', columns: ['user_id', 'entry_id'])
])]
#[ORM\AssociationOverrides([
    new AssociationOverride(name: 'user', inversedBy: 'entryVotes')
])]
#[Cache('NONSTRICT_READ_WRITE')]
class EntryVote extends Vote
{
    #[ManyToOne(targetEntity: Entry::class, inversedBy: 'votes')]
    #[JoinColumn(name: 'entry_id', nullable: false, onDelete: 'cascade')]
    public ?Entry $entry;

    public function __construct(int $choice, User $user, ?Entry $entry)
    {
        parent::__construct($choice, $user, $entry->user);

        $this->entry = $entry;
    }
}
