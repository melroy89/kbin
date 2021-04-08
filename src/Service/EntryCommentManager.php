<?php declare(strict_types=1);

namespace App\Service;

use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Security;
use App\Event\EntryCommentBeforePurgeEvent;
use App\Service\Contracts\ContentManager;
use Doctrine\ORM\EntityManagerInterface;
use App\Event\EntryCommentDeletedEvent;
use App\Event\EntryCommentCreatedEvent;
use App\Event\EntryCommentUpdatedEvent;
use App\Event\EntryCommentPurgedEvent;
use App\Factory\EntryCommentFactory;
use Webmozart\Assert\Assert;
use App\Entity\EntryComment;
use App\DTO\EntryCommentDto;
use App\Entity\User;

class EntryCommentManager implements ContentManager
{
    public function __construct(
        private EntryCommentFactory $commentFactory,
        private EventDispatcherInterface $eventDispatcher,
        private Security $security,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function create(EntryCommentDto $commentDto, User $user): EntryComment
    {
        $comment = $this->commentFactory->createFromDto($commentDto, $user);

        $comment->entry->addComment($comment);
        $comment->magazine = $commentDto->entry->magazine;

        if ($commentDto->image) {
            $comment->image = $commentDto->image;
        }

        $this->entityManager->persist($comment);
        $this->entityManager->flush();

        $this->eventDispatcher->dispatch((new EntryCommentCreatedEvent($comment)));

        return $comment;
    }

    public function edit(EntryComment $comment, EntryCommentDto $commentDto): EntryComment
    {
        Assert::same($comment->entry->getId(), $commentDto->entry->getId());

        $comment->body = $commentDto->body;
        if ($commentDto->image) {
            $comment->image = $commentDto->image;
        }

        $this->entityManager->flush();

        $this->eventDispatcher->dispatch((new EntryCommentUpdatedEvent($comment)));

        return $comment;
    }

    public function delete(EntryComment $comment, bool $trash = false): void
    {
        $trash ? $comment->trash() : $comment->softDelete();

        $this->entityManager->flush();

        $this->eventDispatcher->dispatch((new EntryCommentDeletedEvent($comment, $this->security->getUser())));
    }

    public function purge(EntryComment $comment): void
    {
        $this->eventDispatcher->dispatch((new EntryCommentBeforePurgeEvent($comment)));

        $magazine = $comment->entry->magazine;
        $comment->entry->removeComment($comment);

        $this->entityManager->remove($comment);
        $this->entityManager->flush();

        $this->eventDispatcher->dispatch((new EntryCommentPurgedEvent($magazine)));
    }

    public function createDto(EntryComment $comment): EntryCommentDto
    {
        return $this->commentFactory->createDto($comment);
    }
}
