<?php

declare(strict_types=1);

namespace App\Repository;

use App\Dto\EventInput;
use Doctrine\ORM\EntityRepository;

class EventRepository extends EntityRepository implements WriteEventRepository
{
    public function update(EventInput $eventInput, int $id): void
    {
        $this->createQueryBuilder('e')
            ->update()
            ->set('e.comment', $eventInput->comment)
            ->where('e.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->execute()
        ;
    }
}
