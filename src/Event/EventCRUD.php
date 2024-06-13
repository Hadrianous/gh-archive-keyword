<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\Actor;
use App\Entity\Event;
use App\Entity\EventType;
use App\Entity\Repo;
use App\GHArchive\EventTypeMapper;
use Doctrine\ORM\EntityManagerInterface;

class EventCRUD
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function createEventFromGArchiveDTO(\stdClass $event): void
    {
        $dbEvent = $this->entityManager->find(Event::class, $event->id);
        if ($dbEvent !== null) {
            return;
        }

        $eventType = EventTypeMapper::getEventEntityType($event->type);
        if ($eventType === null) {
            return;
        }

        $actorDto = $event->actor;
        $repoDto = $event->repo;

        $actor = $this->entityManager->find(Actor::class, $actorDto->id);
        if (empty($actor)) {
            $actor = new Actor($actorDto->id, $actorDto->login, $actorDto->url, $actorDto->avatar_url);
            $this->entityManager->persist($actor);
        }

        $repo = $this->entityManager->find(Repo::class, $repoDto->id);
        if (empty($repo)) {
            $repo = new Repo($repoDto->id, $repoDto->name, $repoDto->url);
            $this->entityManager->persist($repo);
        }

        $payload = json_decode(json_encode($event->payload), true);
        $event = new Event((int)$event->id, $eventType, $actor, $repo, $payload, (new \DateTimeImmutable($event->created_at)), $this->getCommentFromDTO($payload, $eventType));
        $this->entityManager->persist($event);
        $this->entityManager->flush();
        unset($payload);
        unset($repo);
        unset($actor);
        unset($event);
        unset($dbEvent);
    }

    private function getCommentFromDTO(array $payload, string $eventType): ?string
    {
        if ($eventType === EventType::COMMIT) {
            return !empty($payload['commits']) ? $payload['commits'][0]['message'] : null;
        } elseif ($eventType === EventType::PULL_REQUEST) {
            return $payload['pull_request']['title'];
        } elseif ($eventType === EventType::COMMENT) {
            return $payload['comment']['body'];
        }

        throw new \InvalidArgumentException('EventType not handled for comment');
    }
}
