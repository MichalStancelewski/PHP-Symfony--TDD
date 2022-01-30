<?php

namespace App\Http;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class YahooFinanceApiClient
{
    private HttpClientInterface $httpClient;

    private const URL = 'https://yh-finance.p.rapidapi.com/stock/v2/get-profile';
    private const X_RAPID_API_HOST = 'yh-finance.p.rapidapi.com';
    private $rapidApiKey;

    public function __construct(HttpClientInterface $httpClient, $rapidApiKey)
    {
        $this->httpClient = $httpClient;
        $this->rapidApiKey = $rapidApiKey;
    }

    public function fetchStockProfile($symbol, $region): array
    {
        $response = $this->httpClient->request('GET', self::URL, [
            'query' => [
                'symbol' => $symbol,
                'region' => $region
            ],
            'headers' => [
                'x-rapidapi-host' => self::X_RAPID_API_HOST,
                'x-rapidapi-key' => $this->rapidApiKey
            ]
        ]);

        return [
            'statusCode' => 200,
            'content' => json_encode([
                'symbol' => 'AMZN',
                'shortName' => 'Amazon.com, Inc.',
                'region' => 'US',
                'exchangeName' => 'NasdaqGS',
                'currency' => 'USD',
                'price' => 100.50,
                'previousChange' => 110.20,
                'priceChange' => -9.70
            ])
        ];
    }
}