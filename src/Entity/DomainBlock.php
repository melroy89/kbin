<?php declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\CreatedAtTrait;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\UniqueConstraint;

#[Entity]
#[Table(uniqueConstraints: [
    new UniqueConstraint(name: 'domain_block_idx', columns: ['user_id', 'domain_id']),
])]
class DomainBlock
{
    use CreatedAtTrait {
        CreatedAtTrait::__construct as createdAtTraitConstruct;
    }

    #[Id]
    #[GeneratedValue]
    #[Column(type: 'integer')]
    private int $id;

    #[ManyToOne(targetEntity: User::class, inversedBy: 'blockedDomains')]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE')]
    public ?User $user;

    #[ManyToOne(targetEntity: Domain::class)]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE')]
    public ?Domain $domain;

    public function __construct(User $user, Domain $domain)
    {
        $this->createdAtTraitConstruct();

        $this->user   = $user;
        $this->domain = $domain;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function __sleep()
    {
        return [];
    }
}
