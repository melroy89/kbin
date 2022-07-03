<?php declare(strict_types=1);

namespace App\Entity;

use App\Repository\AwardTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OrderBy;

#[Entity(repositoryClass: AwardTypeRepository::class)]
class AwardType
{
    #[Id]
    #[GeneratedValue]
    #[Column(type: 'integer')]
    private int $id;

    #[Column(type: 'string', nullable: false)]
    public string $name;

    #[Column(type: 'string', nullable: false)]
    public string $category;

    #[Column(type: 'integer', nullable: false)]
    public int $count = 0;

    #[Column(type: 'array', nullable: false)]
    public array $attributes;

    #[OneToMany(mappedBy: 'type', targetEntity: Award::class, fetch: 'EXTRA_LAZY')]
    #[OrderBy(['createdAt' => 'DESC'])]
    public Collection $awards;

    public function __construct()
    {
        $this->awards = new ArrayCollection();
    }
}
