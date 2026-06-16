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
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
            ]
        ]);
    }

    protected function getHtml(string $url): string
    {
        $response = $this->client->get($url);
        return $response->getBody()->getContents();
    }
}
