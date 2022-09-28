<?php

namespace App\Controller;

use App\Dto\EventInput;
use App\Dto\SearchInput;
use App\Entity\Event;
use App\Repository\ReadEventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class EventController
{
    private SerializerInterface $serializer;
    private EntityManagerInterface $em;
    private ReadEventRepository $repository;
    private ValidatorInterface $validator;

    public function __construct(
        EntityManagerInterface $em,
        ReadEventRepository $readEventRepository,
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ) {
        $this->serializer = $serializer;
        $this->em = $em;
        $this->repository = $readEventRepository;
        $this->validator = $validator;
    }

    /**
     * @Route(path="/api/event", name="api_search", methods={"GET"})
     */
    public function search(Request $request): JsonResponse
    {
        $searchInput = $this->serializer->denormalize($request->query->all(), SearchInput::class);

        $errors = $this->validator->validate($searchInput);

        if (count($errors) > 0) {
            $message = '';
            foreach ($errors as $error) {
                // TODO better error display
                $message .= (string)$error;
            }
            return new JsonResponse($message, Response::HTTP_BAD_REQUEST);
        }

        $countByType = $this->repository->countByType($searchInput);

        $data = [
            'meta' => [
                'totalEvents' => $this->repository->countAll($searchInput),
                'totalPullRequests' => $countByType['pullRequest'] ?? 0,
                'totalCommits' => $countByType['commit'] ?? 0,
                'totalComments' => $countByType['comment'] ?? 0,
            ],
            'data' => [
                'events' => $this->repository->getLatest($searchInput),
                'stats' => $this->repository->statsByTypePerHour($searchInput)
            ]
        ];

        return new JsonResponse($data);
    }

    /**
     * @Route(path="/api/event/{id}", name="api_commit_update", requirements={"id"="\d+"}), methods={"PUT"})
     */
    public function update(Request $request, int $id, ValidatorInterface $validator): Response
    {
        $eventInput = $this->serializer->deserialize($request->getContent(), EventInput::class, 'json');

        $errors = $validator->validate($eventInput);

        if (\count($errors) > 0) {
            return new JsonResponse(
                ['message' => $errors->get(0)->getMessage()],
                Response::HTTP_BAD_REQUEST
            );
        }

        /** @var Event $event */
        if (($event = $this->em->find(Event::class, $id)) === null) {
            return new JsonResponse(
                ['message' => sprintf('Event identified by %d not found !', $id)],
                Response::HTTP_NOT_FOUND
            );
        }

        try {
            $event->setComment($eventInput->comment);
            $this->em->flush();
        } catch (\Exception $exception) {
            return new Response(null, Response::HTTP_SERVICE_UNAVAILABLE);
        }

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
