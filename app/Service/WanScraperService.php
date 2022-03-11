<?php

namespace App\Service;

use App\Lib\Episode;
use Lukaswhite\PodcastFeedParser\Episode as PodcastEpisode;
use Lukaswhite\PodcastFeedParser\Podcast;

class WanScraperService
{
    public function __construct(
        protected WebFeedService $webFeedService,
        protected StoredFeedService $storedFeedService,
        protected DownloadService $downloadService
    ) {
    }

    public function getEpisodes(): void
    {
        $podcast = $this->storedFeedService->getPodcast();
        $webEpisodes = $this->webFeedService->getEpisodes();

        foreach ($webEpisodes as $episode) {
            if (!$this->podcastHasEpisode($podcast, $episode)) {
                $this->addEpisode($podcast, $episode);
            }
        }

        $this->storedFeedService->updateFeed($podcast);
    }

    protected function podcastHasEpisode(Podcast $podcast, Episode $episode): bool
    {
        foreach ($podcast->getEpisodes()->getIterator() as $podcastEpisode) {
            /** @var PodcastEpisode $podcastEpisode */
            if ($podcastEpisode->getLink() === $episode->getLink()) {
                return true;
            }
        }

        return false;
    }

    protected function addEpisode(Podcast $podcast, Episode $episode): void
    {
        $this->downloadService->download($episode);
        $this->storedFeedService->addEpisode($podcast, $episode);
    }
}
