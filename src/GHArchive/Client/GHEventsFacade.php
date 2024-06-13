<?php

declare(strict_types=1);

namespace App\GHArchive\Client;

use App\Event\EventCRUD;
use JsonMachine\Items;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GHEventsFacade
{
    use GHEventsClientTrait;

    private HttpClientInterface $httpClient;

    private const TEMP_PATH = '/tmp/gh/';
    private const TEMP_GZIP_FILENAME = 'archive.json.gz';
    private const TEMP_FILENAME = 'archive.json';
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

    public function saveHourlyEventsFromArchive(\DateTimeImmutable $date, int $hour): void
    {
        $filePath = $this->downloadUnzippedArchive($date, $hour);
        if ($filePath === null) {
            return;
        }
        $events = Items::fromFile($filePath);
        foreach ($events as $eventData) {
            $this->eventCRUD->createEventFromGArchiveDTO($eventData);
        }
    }

    public function downloadUnzippedArchive(\DateTimeImmutable $date, int $hour): ?string
    {
        $gzipFilePath = self::TEMP_PATH . self::TEMP_GZIP_FILENAME;
        $filePath = self::TEMP_PATH . self::TEMP_FILENAME;
        $response = $this->getHourlyEventsFile($date, $hour);
        if ($response->getStatusCode() === Response::HTTP_OK) {
            // TODO split large file in sub file to avoid memory limit
            file_put_contents($gzipFilePath, $response->getContent());
            $zipFile = gzopen($gzipFilePath, 'r');
            $jsonFile = fopen($filePath, 'w');
            fwrite($jsonFile, '[');
            while ($line = gzgets($zipFile)) {
                fwrite($jsonFile, $line . ',');
            }
            fwrite($jsonFile, ']');
            gzclose($zipFile);
            fclose($jsonFile);
            unlink($gzipFilePath);
        } elseif ($response->getStatusCode() === Response::HTTP_NOT_FOUND) {
            // If current day, maybe not all hours files are present
            return null;
        }

        return $filePath;
    }
}
