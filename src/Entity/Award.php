<?php declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\CreatedAtTrait;
use App\Repository\AwardRepository;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;

#[Entity(repositoryClass: AwardRepository::class)]
class Award
{
    use CreatedAtTrait {
        CreatedAtTrait::__construct as createdAtTraitConstruct;
    }

    #[Id]
    #[GeneratedValue]
    #[Column(type: 'integer')]
    private int $id;

    #[ManyToOne(targetEntity: User::class, inversedBy: 'awards')]
    #[JoinColumn(nullable: false, onDelete: 'cascade')]
    public User $user;

    #[ManyToOne(targetEntity: Magazine::class, inversedBy: 'awards')]
    #[JoinColumn(nullable: false, onDelete: 'cascade')]
    public Magazine $magazine;

    #[ManyToOne(targetEntity: AwardType::class, inversedBy: 'awards')]
    #[JoinColumn(nullable: false, onDelete: 'cascade')]
    public AwardType $type;
}
