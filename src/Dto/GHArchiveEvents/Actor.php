<?php

declare(strict_types=1);

namespace App\Dto\GHArchiveEvents;

class Actor
{
    private int $id;
    private string $login;
    private string $url;
    private string $avatarUrl;

    public function getId(): int
    {
        return $this->id;
    }

    public function getLogin(): string
    {
        return $this->login;
    }

    public function getAvatarUrl(): string
    {
        return $this->avatarUrl;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function setLogin(string $login): self
    {
        $this->login = $login;
        return $this;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;
        return $this;
    }

    public function setAvatarUrl(string $avatarUrl): self
    {
        $this->avatarUrl = $avatarUrl;
        return $this;
    }
}
