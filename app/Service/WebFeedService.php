<?php

namespace App\Service;

use App\Lib\Episode;
use App\Lib\SimplePie\SimplePie;

class WebFeedService
{
    public function getEpisodes(): array
    {
        return $this->filterEpisodes($this->getFeedEpisodes());
    }

    protected function filterEpisodes(array $episodes): array
    {
        $searchString = strtolower(config('wanscraper')['search_string']);

        return array_filter($episodes, static fn(Episode $episode): bool => str_contains(strtolower($episode), $searchString));
    }

    protected function getFeedEpisodes(): array
    {
        $feed = $this->getFeed();

        $episodes = [];
        foreach ($feed->get_items() as $item) {
            $episodes[] = Episode::fromSimplePie($item);
        }

        return $episodes;
    }

    protected function getFeed(): SimplePie
    {
        $rss = new SimplePie();
        $rss->set_feed_url(config('wanscraper')['input_feed']);
        $rss->init();

        return $rss;
    }
}
