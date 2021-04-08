<?php declare(strict_types=1);

namespace App\Event;

use App\Entity\EntryComment;

class EntryCommentBeforePurgeEvent
{
    public function __construct(public EntryComment $comment)
    {
    }
}
