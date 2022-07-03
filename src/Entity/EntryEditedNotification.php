<?php declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;

#[Entity]
class EntryEditedNotification extends Notification
{
    #[ManyToOne(targetEntity: Entry::class, inversedBy: 'notifications')]
    #[JoinColumn(nullable: true)]
    public ?Entry $entry;

    public function __construct(User $receiver, Entry $entry)
    {
        parent::__construct($receiver);

        $this->entry = $entry;
    }

    public function getSubject(): Entry
    {
        return $this->entry;
    }

    public function getType(): string
    {
        return 'entry_edited_notification';
    }
}
