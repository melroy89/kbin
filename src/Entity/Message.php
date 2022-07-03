<?php declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\CreatedAtTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;

#[Entity]
class Message
{
    const STATUS_NEW = 'new';
    const STATUS_READ = 'read';

    use CreatedAtTrait {
        CreatedAtTrait::__construct as createdAtTraitConstruct;
    }

    #[Id]
    #[GeneratedValue]
    #[Column(type: 'integer')]
    private int $id;

    #[ManyToOne(targetEntity: MessageThread::class, cascade: ['persist'], inversedBy: 'messages')]
    #[JoinColumn(nullable: false)]
    public MessageThread $thread;

    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(nullable: false)]
    public User $sender;

    #[Column(type: 'text', nullable: false)]
    public string $body;

    #[Column(type: 'string', nullable: false)]
    public string $status = self::STATUS_NEW;

    #[OneToMany(mappedBy: 'message', targetEntity: MessageNotification::class, cascade: ['remove'], orphanRemoval: true)]
    private Collection $notifications;

    public function __construct(MessageThread $thread, User $sender, string $body)
    {
        $this->thread        = $thread;
        $this->sender        = $sender;
        $this->body          = $body;
        $this->notifications = new ArrayCollection();

        $thread->addMessage($this);

        $this->createdAtTraitConstruct();
    }

    public function getId(): int
    {
        return $this->id;
    }
}
