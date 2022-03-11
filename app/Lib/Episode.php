<?php

namespace App\Lib;

use App\Lib\SimplePie\Item;

class Episode
{
    private string $link = '';
    private string $title = '';
    private string $description = '';
    private \DateTime $pubDate;
    private ?string $contentUrl = null;
    private ?\SplFileInfo $localFile = null;
    private ?string $imageLink = null;

    public function getLink(): string
    {
        return $this->link;
    }

    public function setLink(string $link): self
    {
        $this->link = $link;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getPubDate(): \DateTime
    {
        return $this->pubDate;
    }

    public function setPubDate(\DateTime $pubDate): self
    {
        $this->pubDate = $pubDate;

        return $this;
    }

    public function getContentUrl(): ?string
    {
        return $this->contentUrl;
    }

    public function setContentUrl(string $contentUrl): self
    {
        $this->contentUrl = $contentUrl;

        return $this;
    }

    public function getLocalFile(): ?\SplFileInfo
    {
        return $this->localFile;
    }

    public function setLocalFile(\SplFileInfo $localFile): self
    {
        $this->localFile = $localFile;

        return $this;
    }

    public function getImageLink(): ?string
    {
        return $this->imageLink;
    }

    public function setImageLink(?string $imageLink): self
    {
        $this->imageLink = $imageLink;

        return $this;
    }

    public static function fromSimplePie(Item $item): self
    {
        return (new Episode())
            ->setTitle($item->get_title())
            ->setLink($item->get_permalink())
            ->setDescription($item->get_media_description())
            ->setPubDate(new \DateTime($item->get_date()))
            ->setImageLink($item->get_thumbnail_url());
    }
}
