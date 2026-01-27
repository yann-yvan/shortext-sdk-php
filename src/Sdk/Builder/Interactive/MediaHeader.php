<?php

namespace NyCorp\Shortext\Sdk\Builder\Interactive;

trait MediaHeader
{
    public function addImageHeader(string $url): self
    {
        return $this->addHeader('image', $url);
    }

    public function addVideoHeader(string $url): self
    {
        return $this->addHeader('video', $url);
    }

    public function addDocumentHeader(string $url): self
    {
        return $this->addHeader('document', $url);
    }
}
