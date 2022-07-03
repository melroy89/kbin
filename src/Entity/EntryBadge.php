<?php declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;

#[Entity]
class EntryBadge
{
    #[Id]
    #[GeneratedValue]
    #[Column(type: 'integer')]
    private int $id;

    #[ManyToOne(targetEntity: Badge::class, inversedBy: 'badges')]
    #[JoinColumn(nullable: false)]
    public Badge $badge;

    #[ManyToOne(targetEntity: Entry::class, inversedBy: 'badges')]
    #[JoinColumn(nullable: false)]
    public Entry $entry;

    public function __construct(Entry $entry, Badge $badge)
    {
        $this->entry = $entry;
        $this->badge = $badge;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function __toString(): string
    {
        return $this->badge->name;
    }
}
