<?php declare(strict_types=1);

namespace App\Event;

use App\Entity\PostComment;

class PostCommentUpdatedEvent
{
    public function __construct(public PostComment $comment)
    {
    }
}
