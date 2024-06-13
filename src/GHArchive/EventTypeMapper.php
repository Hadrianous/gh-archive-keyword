<?php

declare(strict_types=1);

namespace App\GHArchive;

use App\Dto\GHArchiveEvents\EventType as EventTypeDTO;
use App\Entity\EventType;

class EventTypeMapper
{
    private const EVENT_TYPE_MAP = [
        EventTypeDTO::COMMIT_COMMENT_EVENT => EventType::COMMENT,
        EventTypeDTO::PULL_REQUEST_EVENT => EventType::PULL_REQUEST,
        EventTypeDTO::PUSH_EVENT => EventType::COMMIT,
    ];

    public static function getEventEntityType(string $dtoEventType): ?string
    {
        return self::EVENT_TYPE_MAP[$dtoEventType] ?? null;
    }
}
