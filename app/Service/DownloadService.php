<?php

namespace App\Service;

use App\Exception\DownloadException;
use App\Lib\Episode;
use YoutubeDl\Entity\Video;
use YoutubeDl\Options;
use YoutubeDl\YoutubeDl;

class DownloadService
{
    public function __construct(protected YoutubeDl $dl)
    {
    }

    public function download(Episode $episode): void
    {
        $options = $this->createOptions($episode->getLink());

        $collection = $this->dl->download($options);

        $errors = [];
        foreach ($collection->getVideos() as $video) {
            if ($video->getError() !== null) {
                $errors[] = new DownloadException($video->getError());
            } else {
                $this->updateEpisode($episode, $video);

                return;
            }
        }
        if (count($errors) > 0) {
            throw reset($errors);
        }
    }

    protected function updateEpisode(Episode $episode, Video $video): void
    {
        $episode->setContentUrl(sprintf('%s/%s', config('download')['base_url'], $video->getFile()->getFilename()))
            ->setLocalFile($video->getFile());
    }

    protected function createOptions(string $url): Options
    {
        return Options::create()
            ->format('bestaudio')
            ->extractAudio(true)
            ->audioFormat(Options::AUDIO_FORMAT_MP3)
            ->audioQuality(0)
            ->downloadPath(config('download')['path'])
            ->url($url);
    }
}