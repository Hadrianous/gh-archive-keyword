<?php

declare(strict_types=1);

namespace App\Tests\Unit\Event;

use App\Dto\GHArchiveEvents\EventType;
use App\Entity\Actor;
use App\Entity\Event;
use App\Entity\Repo;
use App\Event\EventCRUD;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class EventCRUDTest extends KernelTestCase
{
    public function setUp():void
    {
        self::bootKernel();
    }

    public function testEventAlreadyExists()
    {
        $mockEntityManager = $this->getMockBuilder(EntityManager::class)
            ->onlyMethods(['getRepository', 'persist', 'flush', 'find'])
            ->disableOriginalConstructor()
            ->getMock();

        $crudService = new EventCRUD($mockEntityManager);
        $event = new \stdClass();
        $event->id = '1';

        $mockEntityManager
            ->expects($this->once())
            ->method('find')
            ->with(Event::class, $event->id)
            ->willReturn(1);

        $crudService->createEventFromGArchiveDTO($event);
    }

    public function testEventTypeNotExists()
    {
        $mockEntityManager = $this->getMockBuilder(EntityManager::class)
            ->onlyMethods(['getRepository', 'persist', 'flush', 'find'])
            ->disableOriginalConstructor()
            ->getMock();

        $crudService = new EventCRUD($mockEntityManager);
        $event = new \stdClass();
        $event->id = '1';
        $event->type = 'none';

        $mockEntityManager
            ->expects($this->once())
            ->method('find')
            ->with(Event::class, $event->id)
            ->willReturn(null);

        $crudService->createEventFromGArchiveDTO($event);
    }

    /**
     * @dataProvider getEventData
     */
    public function testSaveEvent(string $type)
    {
        $event = new \stdClass();
        $event->id = '1';
        $event->type = $type;
        $event->created_at = "2022-09-27T01:00:00Z";

        // TODO handle different payload
        if ($type === EventType::PULL_REQUEST_EVENT) {
            $event->payload = new \stdClass();
            $event->payload->pull_request = new \stdClass();
            $event->payload->pull_request->title = 'test';
        }
        $event->actor = new \stdClass();
        $event->actor->id = 2;
        $event->actor->login = 'michel';
        $event->actor->url = 'https://osef.com';
        $event->actor->avatar_url = 'https://avatar_osef.com';
        $event->repo = new \stdClass();
        $event->repo->id = 3;
        $event->repo->name = 'top_repo';
        $event->repo->url = 'https://osef.repo.com';

        $mockEntityManager = $this->getMockBuilder(EntityManager::class)
            ->onlyMethods(['getRepository', 'persist', 'flush', 'find'])
            ->disableOriginalConstructor()
            ->getMock();

        $crudService = new EventCRUD($mockEntityManager);

        $mockEntityManager
            ->expects($this->exactly(3))
            ->method('find')
            ->withConsecutive([Event::class, $event->id], [Actor::class, $event->actor->id], [Repo::class, $event->repo->id])
            ->willReturnOnConsecutiveCalls(null, null, null);

        // TODO check withConsecutive saved entities
        $mockEntityManager
            ->expects($this->exactly(3))
            ->method('persist');

        $mockEntityManager
            ->expects($this->once())
            ->method('flush');

        $crudService->createEventFromGArchiveDTO($event);
    }

    public function getEventData(): array
    {
        return [
//            'commit' => [
//                'type' => EventType::COMMIT_COMMENT_EVENT,
//            ],
//            'push' => [
//                'type' => EventType::PUSH_EVENT,
//            ],
            'pull_request' => [
                'type' => EventType::PULL_REQUEST_EVENT,
            ],
        ];
    }
}
