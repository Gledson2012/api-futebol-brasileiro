<?php

namespace App\Scrapers;

use GuzzleHttp\Client;

abstract class BaseScraper
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client([
            'headers' => [
                'User-Agent' => 'Mozilla/5.0'
            ]
        ]);
    }

    protected function getHtml(string $url): string
    {
        $response = $this->client->get($url);
        return (string) $response->getBody();
    }
}
