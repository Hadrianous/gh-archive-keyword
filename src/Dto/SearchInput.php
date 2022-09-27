<?php

namespace App\Dto;

class SearchInput
{
    public \DateTimeImmutable $date;

    public string $keyword;

    public function seyKeyword(string $keyword): self
    {
        $this->keyword = strip_tags($keyword);
        return $this;
    }
}
