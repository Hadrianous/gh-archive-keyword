<?php

declare(strict_types=1);

namespace App\GHArchive\Client;

use App\Dto\GHArchiveEvents\Event;
use App\Event\EventCRUD;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GHEventsFacade
{
    use GHEventsClientTrait;

    private HttpClientInterface $httpClient;

    private const TEMP_PATH = '/tmp/gh/';
    private const TEMP_FILENAME = 'archive.json.gz';
    private string $endpoint;
    private SerializerInterface $serializer;
    private EventCRUD $eventCRUD;
    private ValidatorInterface $validator;

    public function __construct(
        HttpClientInterface $httpClient,
        array $ghArchiveConfig,
        SerializerInterface $serializer,
        EventCRUD $eventCRUD,
        ValidatorInterface $validator
    ) {
        $this->httpClient = $httpClient;
        $this->createDirectory(self::TEMP_PATH);
        $this->endpoint = $ghArchiveConfig['url'];
        $this->serializer = $serializer;
        $this->eventCRUD = $eventCRUD;
        $this->validator = $validator;
    }

    private function createDirectory(string $directory): bool
    {
        return !(!is_dir($directory) && !@mkdir($directory, 0777, true) && !is_dir($directory));
    }

    public function saveDailyEvents(\DateTimeImmutable $date)
    {
        $filePath = self::TEMP_PATH . self::TEMP_FILENAME;
        for ($i = 1; $i < 2; $i++) {
            $response = $this->getHourlyEventsFile($date, $i);
            if ($response->getStatusCode() === Response::HTTP_OK) {
                file_put_contents($filePath, $response->getContent());
                $zh = gzopen($filePath, 'r');
                while ($line = gzgets($zh)) {
                    $this->saveEventFromJson($line);
                }
                gzclose($zh);
                unlink($filePath);
            } elseif ($response->getStatusCode() === Response::HTTP_NOT_FOUND) {
                // If current day, maybe not all hours files are present
                break;
            }
        }
    }

    private function saveEventFromJson($jsonData) {
        $event = $this->serializer->deserialize($jsonData, Event::class, 'json');
        $errors = $this->validator->validate($event);
        if (count($errors) > 0) {
            return;
        }
        $this->eventCRUD->createEventFromGArchiveDTO($event);
        unset($event);
    }
}
