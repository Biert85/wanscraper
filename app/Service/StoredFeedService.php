<?php

namespace App\Service;

use App\Lib\Episode;
use App\Writer\FeedWriter;
use Illuminate\Support\Facades\Storage;
use Lukaswhite\PodcastFeedParser\Artwork;
use Lukaswhite\PodcastFeedParser\Episode as PodcastEpisode;
use Lukaswhite\PodcastFeedParser\Image;
use Lukaswhite\PodcastFeedParser\Link;
use Lukaswhite\PodcastFeedParser\Media;
use Lukaswhite\PodcastFeedParser\Owner;
use Lukaswhite\PodcastFeedParser\Parser;
use Lukaswhite\PodcastFeedParser\Podcast;
use Ramsey\Uuid\Uuid;

class StoredFeedService
{
    public function getPodcast(): Podcast
    {
        $path = $this->getFeedPath();
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

        $podcast = (new Podcast())
            ->setLink($config['link'])
            ->setTitle($config['title'])
            ->setDescription($config['description'])
            ->setAuthor($config['author'])
            ->setCopyright($config['copyright'])
            ->setLanguage($config['language'])
            ->setLastBuildDate(new \DateTime('now'))
            ->setNewFeedUrl($config['feed_url']);

        $podcast->addAtomLink((new Link($config['feed_url']))
            ->setRel('self')
            ->setType('application/atom+xml')
        );
        $podcast->setImage((new Image())
            ->setTitle($config['title'])
            ->setLink($config['link'])
            ->setUrl($config['image'])
        );
        $podcast->setOwner((new Owner())
            ->setEmail($config['email'])
            ->setName($config['copyright'])
        );

        return $podcast;
    }

    public function addEpisode(Podcast $podcast, Episode $episode): void
    {
        $media = new Media();
        $media->setUri($episode->getContentUrl())
            ->setMimeType('audio/mpeg')
            ->setLength($episode->getLocalFile()->getSize());

        $artwork = new Artwork();
        $artwork->setUri($episode->getImageLink());

        $podcastEpisode = new PodcastEpisode();
        $podcastEpisode
            ->setLink($episode->getLink())
            ->setTitle($episode->getTitle())
            ->setDescription($episode->getDescription())
            ->setPublishedDate($episode->getPubDate())
            ->setMedia($media)
            ->setGuid(Uuid::uuid4())
            ->setDuration($episode->getDuration())
            ->setArtwork($artwork);

        $podcast->getEpisodes()->add($podcastEpisode);
        $podcast->setLastBuildDate(new \DateTime('now'));
    }

    public function updateFeed(Podcast $podcast): void
    {
        $writer = new FeedWriter($this->getFeedPath());
        $writer->write($podcast);
    }

    protected function getFeedPath(): string
    {
        return config('wanscraper')['output_feed'];
    }
}
