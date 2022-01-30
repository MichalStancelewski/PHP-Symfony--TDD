<?php

namespace App\Tests\integration;

use App\Tests\DatabaseDependantTestCase;

class YahooFinanceApiClientTest extends DatabaseDependantTestCase
{
    /**
     * @test
     * @group integration
     */
    public function the_yahoo_finance_api_client_returns_the_correct_data()
    {
        // Set up
        $yahooFinanceApiClient = self::$kernel->getContainer()->get('yahoo-finance-api-client');

        // Do something
        $response = $yahooFinanceApiClient->fetchStockProfile('AMZN', 'US');

        $stockProfile = json_decode($response['content']);

        // Assertions
        $this->assertSame('AMZN', $stockProfile->symbol);
        $this->assertSame('Amazon.com, Inc.', $stockProfile->shortName);
        $this->assertSame('US', $stockProfile->region);
        $this->assertSame('NasdaqGS', $stockProfile->exchangeName);
        $this->assertSame('USD', $stockProfile->currency);
        $this->assertIsFloat($stockProfile->price);
        $this->assertIsFloat($stockProfile->previousChange);
        $this->assertIsFloat($stockProfile->priceChange);
    }

}