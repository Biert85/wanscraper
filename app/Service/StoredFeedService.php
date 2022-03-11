<?php

namespace App\Service;

use App\Lib\Episode;
use Illuminate\Support\Facades\Storage;
use Lukaswhite\PodcastFeedParser\Episode as PodcastEpisode;
use Lukaswhite\PodcastFeedParser\Image;
use Lukaswhite\PodcastFeedParser\Media;
use Lukaswhite\PodcastFeedParser\Parser;
use Lukaswhite\PodcastFeedParser\Podcast;
use Ramsey\Uuid\Uuid;

class StoredFeedService
{
    public function getPodcast(): Podcast
    {
        $path = sprintf('%s/%s', config('app')['storage_path'], config('wanscraper')['output_feed']);
        if (!Storage::exists($path)) {
            return $this->createPodcast();
        }

        return $this->parseFile($path);
    }

    protected function parseFile(string $path): Podcast
    {
        $parser = new Parser();
        $parser->setContent(Storage::get($path));

        return $parser->run();
    }

    protected function createPodcast(): Podcast
    {
        $config = config('feed');

        return (new Podcast())
            ->setLink($config['link'])
            ->setTitle($config['title'])
            ->setDescription($config['description'])
            ->setImage($config['image'])
            ->setAuthor($config['author'])
            ->setCopyright($config['copyright'])
            ->setLanguage($config['language']);
    }

    public function addEpisode(Podcast $podcast, Episode $episode): void
    {
        $media = new Media();
        $media->setUri($episode->getContentUrl())
            ->setMimeType('audio/mpeg')
            ->setLength($episode->getLocalFile()->getSize());

        $image = new Image();
        $image->setLink($episode->getImageLink())
            ->setUrl($episode->getImageLink())
            ->setTitle($episode->getTitle());

        $podcastEpisode = new PodcastEpisode();
        $podcastEpisode->setLink($episode->getLink())
            ->setTitle($episode->getTitle())
            ->setDescription($episode->getDescription())
            ->setPublishedDate($episode->getPubDate())
            ->setMedia($media)
            ->setImage($image)
            ->setGuid(Uuid::uuid4());

        $podcast->getEpisodes()->add($podcastEpisode);
    }
}
