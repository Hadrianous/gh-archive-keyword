<?php

declare(strict_types=1);

namespace App\Event;

use App\Dto\GHArchiveEvents\Event as EventDTO;
use App\Entity\Actor;
use App\Entity\Event;
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

    public function createEventFromGArchiveDTO(EventDTO $event): void
    {
        $dbEvent = $this->entityManager->find(Event::class, $event->getId());
        if ($dbEvent !== null) {
            return;
        }

        $eventType = EventTypeMapper::getEventEntityType($event->getType());
        if ($eventType === null) {
            return;
        }

        $actorDto = $event->getActor();
        $repoDto = $event->getRepo();

        $actor = $this->entityManager->find(Actor::class, $actorDto->getId());
        if (empty($actor)) {
            $actor = new Actor($actorDto->getId(), $actorDto->getLogin(), $actorDto->getAvatarUrl(), $actorDto->getAvatarUrl());
            $this->entityManager->persist($actor);
        }

        $repo = $this->entityManager->find(Repo::class, $repoDto->getId());
        if (empty($repo)) {
            $repo = new Repo($repoDto->getId(), $repoDto->getName(), $repoDto->getUrl());
            $this->entityManager->persist($repo);
        }

        $event = new Event((int)$event->getId(), $eventType, $actor, $repo, $event->getPayload(), $event->getCreatedAt(), $event->getComment());
        $this->entityManager->persist($event);
        $this->entityManager->flush();
    }
}
