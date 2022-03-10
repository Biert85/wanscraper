<?php

namespace App\FeedIo\Adapter;

use DateTime;
use FeedIo\Adapter\ClientInterface;
use FeedIo\Adapter\Guzzle\Client;
use FeedIo\Adapter\Guzzle\Response;
use FeedIo\Adapter\NotFoundException;
use FeedIo\Adapter\ResponseInterface;
use FeedIo\Adapter\ServerErrorException;
use GuzzleHttp\TransferStats;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class HttpAdapter implements ClientInterface
{
    /**
     * @throws \Exception
     */
    public function getResponse(string $url, DateTime $modifiedSince = null): ResponseInterface
    {
        $headResponse = $this->request('head', $url, $modifiedSince);
        if ($headResponse->getStatusCode() === SymfonyResponse::HTTP_NOT_MODIFIED) {
            return $headResponse;
        }

        return $this->request('get', $url, $modifiedSince);
    }

    /**
     * @throws \Exception
     */
    protected function request(string $method, string $url, DateTime $modifiedSince = null): ResponseInterface
    {
        $headers = $this->getHeaders($modifiedSince);
        $options = $this->getOptions();

        $duration = 0;
        $options['on_stats'] = static function (TransferStats $stats) use (&$duration) {
            $duration = $stats->getTransferTime();
        };

        $psrResponse = Http::withOptions($options)
            ->withHeaders($headers)
            ->send($method, $url)
            ->toPsrResponse();

        switch ($psrResponse->getStatusCode()) {
            case 200:
            case 304:
                return new Response($psrResponse, $duration);
            case 404:
                throw new NotFoundException('not found', $duration);
            default:
                throw new ServerErrorException($psrResponse, $duration);
        }
    }

    protected function getHeaders(?\DateTime $modifiedSince): array
    {
        $headers = [
            'Accept-Encoding' => 'gzip, deflate',
            'User-Agent' => Client::DEFAULT_USER_AGENT,
        ];
        if ($modifiedSince !== null) {
            $headers['If-Modified-Since'] = $modifiedSince->format(\DateTimeInterface::RFC2822);
        }

        return $headers;
    }

    protected function getOptions(): array
    {
        return [
            'http_errors' => false,
            'timeout' => 30,
        ];
    }
}
