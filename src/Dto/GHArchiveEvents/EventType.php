<?php

declare(strict_types=1);

namespace App\Dto\GHArchiveEvents;

class EventType
{
    public const PUSH_EVENT = 'PushEvent';
    public const COMMIT_COMMENT_EVENT = 'CommitCommentEvent';
    public const PULL_REQUEST_EVENT = 'PullRequestEvent';

    public function getTypes()
    {
        return [
            self::PUSH_EVENT,
            self::COMMIT_COMMENT_EVENT,
            self::PULL_REQUEST_EVENT,
        ];
    }
}
