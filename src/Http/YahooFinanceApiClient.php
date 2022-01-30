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

        if ($response->getStatusCode() !== 200) {
            // @todo
        }

        $stockProfile = json_decode($response->getContent())->price;

        $stockProfileAsArray = [
            'symbol' => $stockProfile->symbol,
            'shortName' => $stockProfile->shortName,
            'region' => $region,
            'exchangeName' => $stockProfile->exchangeName,
            'currency' => $stockProfile->currency,
            'price' => $stockProfile->regularMarketPrice->raw,
            'previousChange' => $stockProfile->regularMarketPreviousClose->raw,
            'priceChange' => $stockProfile->regularMarketPrice->raw - $stockProfile->regularMarketPreviousClose->raw
        ];

        return [
            'statusCode' => 200,
            'content' => json_encode($stockProfileAsArray)
        ];
    }
}