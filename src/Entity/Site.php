<?php declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;

#[Entity]
class Site
{
    #[Id]
    #[GeneratedValue]
    #[Column(type: 'integer')]
    private int $id;

    #[Column(type: 'string', nullable: true)]
    public string $domain;

    #[Column(type: 'string', nullable: true)]
    public string $title;

    #[Column(type: 'string', nullable: true)]
    public ?string $description;

    #[Column(type: 'string', nullable: true)]
    public ?string $terms = null;

    #[Column(type: 'string', nullable: true)]
    public ?string $privacyPolicy = null;

    #[Column(type: 'boolean', nullable: true)]
    public bool $enabled;

    #[Column(type: 'boolean', nullable: true)]
    public bool $registrationOpen;

    public function getId(): ?int
    {
        return $this->id;
    }
}
