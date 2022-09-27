<?php

declare(strict_types=1);

namespace App\GHArchive\Client;

use Symfony\Contracts\HttpClient\ResponseInterface;

trait GHEventsClientTrait
{
    private function getHourlyEventsFile(\DateTimeImmutable $date, int $hour): ResponseInterface
    {
        return $this->httpClient->request('GET', $this->endpoint . '/' . $date->format('Y-m-d') . '-' . $hour . '.json.gz');
    }
}
