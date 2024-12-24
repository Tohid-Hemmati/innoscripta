<?php

namespace App\DTO;

class NewsDTO
{
    public function __construct(
        public string  $title,
        public string  $content,
        public ?string $source = null,
        public ?string $source_url = null,
        public ?string $author = null,
        public string  $published_at,
        public ?string $metadata = null,
    )
    {
    }
}
