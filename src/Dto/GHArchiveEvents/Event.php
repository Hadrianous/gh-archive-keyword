<?php

declare(strict_types=1);

namespace App\Dto\GHArchiveEvents;

use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

class Event
{
    private string $id;
    /**
     * TODO change for shared list
     * @Assert\Choice({"PushEvent", "CommitCommentEvent", "PullRequestEvent"})
     */
    private string $type;
    private Repo $repo;
    private Actor $actor;

    /**
     * @SerializedName("created_at")
     */
    private \DateTimeImmutable $createdAt;

    /**
     * @SerializedName("message")
     */
    private ?string $comment = null;

    private array $payload;

    public function getRepo(): Repo
    {
        return $this->repo;
    }

    public function getActor(): Actor
    {
        return $this->actor;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function setId(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function setRepo(?Repo $repo): self
    {
        $this->repo = $repo;
        return $this;
    }

    public function setActor(?Actor $actor): self
    {
        $this->actor = $actor;
        return $this;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;
        return $this;
    }

    public function setPayload(array $payload): self
    {
        $this->payload = $payload;
        return $this;
    }
}
