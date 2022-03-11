<?php

namespace App\Writer;

use Illuminate\Support\Facades\Storage;
use Lukaswhite\PodcastFeedParser\Episode;
use Lukaswhite\PodcastFeedParser\Podcast;

class FeedWriter
{
    public const NS_ATOM = 'atom';
    public const NS_CONTENT = 'content';
    public const NS_ITUNES = 'itunes';

    public const NAMESPACES = [
        self::NS_ATOM => 'http://www.w3.org/2005/Atom',
        self::NS_CONTENT => 'http://purl.org/rss/1.0/modules/content/',
        self::NS_ITUNES => 'http://www.itunes.com/dtds/podcast-1.0.dtd',
    ];

    private \XMLWriter $xml;

    private string $version = '1.0';
    private string $encoding = 'UTF-8';

    public function __construct(private string $path)
    {
        $this->xml = new \XMLWriter();
    }

    public function write(Podcast $podcast): void
    {
        $this->xml->openMemory();
        $this->xml->startDocument($this->version, $this->encoding);

        $this->xml->startElement('rss');
        $this->xml->writeAttribute('version', '2.0');

        $this->writeChannel($podcast);

        $this->xml->endElement(); // rss
        $this->xml->endDocument();

        Storage::put($this->path, $this->xml->flush());
    }

    private function writeChannel(Podcast $podcast): void
    {
        $this->xml->startElement('channel');

        foreach ($podcast->getAtomLinks() as $link) {
            $this->xml->startElementNs(self::NS_ATOM, 'link', self::ns(self::NS_ATOM));
            $this->xml->writeAttribute('href', $link->getUri());
            $this->xml->writeAttribute('rel', $link->getRel());
            $this->xml->writeAttribute('type', $link->getType());
            $this->xml->endElement(); // atom:link
        }

        $this->xml->startElement('title');
        $this->xml->writeCdata($podcast->getTitle());
        $this->xml->endElement(); //title
        $this->xml->writeElement('description', $podcast->getDescription());
        $this->xml->writeElement('copyright', $podcast->getCopyright());
        $this->xml->writeElement('language', $podcast->getLanguage());
        $this->xml->writeElement('pubDate', $podcast->getLastBuildDate()->format(\DateTimeInterface::RFC2822));
        $this->xml->writeElement('lastBuildDate', $podcast->getLastBuildDate()->format(\DateTimeInterface::RFC2822));
        $this->xml->writeElement('link', $podcast->getLink());

        $this->xml->startElement('image');
        $this->xml->writeElement('link', $podcast->getImage()->getLink());
        $this->xml->startElement('title');
        $this->xml->writeCdata($podcast->getImage()->getTitle());
        $this->xml->endElement(); // title
        $this->xml->writeElement('url', $podcast->getImage()->getUrl());
        $this->xml->endElement(); // image

        $this->xml->writeElementNs(self::NS_ITUNES, 'type', self::ns(self::NS_ITUNES), 'episodic');
        $this->xml->startElementNs(self::NS_ITUNES, 'summary', self::ns(self::NS_ITUNES));
        $this->xml->writeCdata($podcast->getDescription());
        $this->xml->endElement(); // itunes:summary
        $this->xml->writeElementNs(self::NS_ITUNES, 'author', self::ns(self::NS_ITUNES), $podcast->getAuthor());
        $this->xml->writeElementNs(self::NS_ITUNES, 'explicit', self::ns(self::NS_ITUNES), 'no');
        $this->xml->startElementNs(self::NS_ITUNES, 'image', self::ns(self::NS_ITUNES));
        $this->xml->writeAttribute('href', $podcast->getImage()->getUrl());
        $this->xml->endElement(); // itunes:image
        $this->xml->writeElementNs(self::NS_ITUNES, 'new-feed-url', self::ns(self::NS_ITUNES), $podcast->getNewFeedUrl());
        $this->xml->startElementNs(self::NS_ITUNES, 'owner', self::ns(self::NS_ITUNES));
        $this->xml->writeElementNs(self::NS_ITUNES, 'name', self::ns(self::NS_ITUNES), $podcast->getOwner()->getName());
        $this->xml->writeElementNs(self::NS_ITUNES, 'email', self::ns(self::NS_ITUNES), $podcast->getOwner()->getEmail());
        $this->xml->endElement(); // itunes:owner

        $this->writeItems($podcast);

        $this->xml->endElement(); // channel
    }

    private function writeItems(Podcast $podcast): void
    {
        foreach ($podcast->getEpisodes()->getIterator() as $episode) {
            $this->writeEpisode($episode);
        }
    }

    private function writeEpisode(Episode $episode): void
    {
        $this->xml->startElement('item');

        $this->xml->startElement('guid');
        $this->xml->writeAttribute('isPermaLink', 'false');
        $this->xml->text($episode->getGuid());
        $this->xml->endElement(); // guid
        $this->xml->startElement('title');
        $this->xml->writeCdata($episode->getTitle());
        $this->xml->endElement(); // title
        $this->xml->startElement('description');
        $this->xml->writeCdata($episode->getDescription());
        $this->xml->endElement(); // description
        $this->xml->writeElement('pubDate', $episode->getPublishedDate()->format(\DateTimeInterface::RFC2822));
        $this->xml->writeElement('author', $episode->getAuthor());
        $this->xml->writeElement('link', $episode->getLink());
        $this->xml->startElementNs(self::NS_CONTENT, 'encoded', self::ns(self::NS_CONTENT));
        $this->xml->writeCdata($episode->getDescription());
        $this->xml->endElement(); // content:encoded
        $this->xml->startElement('enclosure');
        $this->xml->writeAttribute('length', $episode->getMedia()->getLength());
        $this->xml->writeAttribute('type', $episode->getMedia()->getMimeType());
        $this->xml->writeAttribute('url', $episode->getMedia()->getUri());
        $this->xml->endElement(); // enclosure
        $this->xml->startElementNs(self::NS_ITUNES, 'title', self::ns(self::NS_ITUNES));
        $this->xml->writeCdata($episode->getTitle());
        $this->xml->endElement(); // itunes:title
        $this->xml->writeElementNs(self::NS_ITUNES, 'author', self::ns(self::NS_ITUNES), $episode->getAuthor());
        $this->xml->startElementNs(self::NS_ITUNES, 'image', self::ns(self::NS_ITUNES));
        $this->xml->writeAttribute('href', $episode->getArtwork()->getUri());
        $this->xml->endElement(); // itunes:image
        $this->xml->writeElementNs(self::NS_ITUNES, 'duration', self::ns(self::NS_ITUNES), $episode->getDuration());
        $this->xml->startElementNs(self::NS_ITUNES, 'summary', self::ns(self::NS_ITUNES));
        $this->xml->writeCdata($episode->getDescription());
        $this->xml->endElement(); // itunes:summary
        $this->xml->startElementNs(self::NS_ITUNES, 'subtitle', self::ns(self::NS_ITUNES));
        $this->xml->writeCdata($episode->getDescription());
        $this->xml->endElement(); // itunes:subtitle
        $this->xml->writeElementNs(self::NS_ITUNES, 'explicit', self::ns(self::NS_ITUNES), 'no');
        $this->xml->writeElementNs(self::NS_ITUNES, 'episodeType', self::ns(self::NS_ITUNES), 'full');

        $this->xml->endElement(); // item
    }

    private static function ns(string $ns): string
    {
        return self::NAMESPACES[$ns];
    }

    public function getVersion(): float|string
    {
        return $this->version;
    }

    public function setVersion(string $version): FeedWriter
    {
        $this->version = $version;

        return $this;
    }

    public function getEncoding(): string
    {
        return $this->encoding;
    }

    public function setEncoding(string $encoding): FeedWriter
    {
        $this->encoding = $encoding;

        return $this;
    }
}
