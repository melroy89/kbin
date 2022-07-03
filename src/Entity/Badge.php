<?php declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;

#[Entity]
class Badge
{
    #[Id]
    #[GeneratedValue]
    #[Column(type: 'integer')]
    private int $id;

    #[ManyToOne(targetEntity: Magazine::class, inversedBy: 'badges')]
    #[JoinColumn(nullable: false, onDelete: 'cascade')]
    public Magazine $magazine;

    #[Column(type: 'string', nullable: false)]
    public ?string $name;

    #[OneToMany(mappedBy: 'badge', targetEntity: EntryBadge::class, cascade: ['remove'], orphanRemoval: true)]
    public Collection $badges;

    public function __construct(Magazine $magazine, string $name)
    {
        $this->magazine = $magazine;
        $this->name     = $name;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function countBadges(): int
    {
        return $this->badges->count();
    }
}
