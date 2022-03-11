<?php

namespace App\Service;

use App\Lib\Episode;
use App\Lib\SimplePie\SimplePie;
use Illuminate\Support\Facades\Log;

class WebFeedService
{
    public function getEpisodes(): array
    {
        return $this->filterEpisodes($this->getFeedEpisodes());
    }

    protected function filterEpisodes(array $episodes): array
    {
        $searchString = strtolower(config('wanscraper')['search_string']);

        return array_filter($episodes, static fn(Episode $episode): bool => str_contains(strtolower($episode->getTitle()), $searchString));
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
        Log::info('Fetching web feed');
        $rss = new SimplePie();
        $rss->set_feed_url(config('wanscraper')['input_feed']);
        $rss->init();

        return $rss;
    }
}
