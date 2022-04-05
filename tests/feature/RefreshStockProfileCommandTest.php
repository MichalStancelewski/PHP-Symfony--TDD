<?php

namespace App\Tests\feature;

use App\Entity\Stock;
use App\Http\FakeYahooFinanceApiClient;
use App\Tests\DatabaseDependantTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class RefreshStockProfileCommandTest extends DatabaseDependantTestCase
{
    /** @test */
    public function the_refresh_stock_profile_command_behaves_correctly_when_a_stock_record_does_not_exist()
    {
        // Set up
        $application = new Application(self::$kernel);

        //Command -> symfony console make:command
        $command = $application->find('app:refresh-stock-profile');

        $commandTester = new CommandTester($command);

        // Set faked return content
        FakeYahooFinanceApiClient::$content = '{"symbol":"AMZN","shortName":"Amazon.com, Inc.","region":"US","exchangeName":"NasdaqGS","currency":"USD","price":3271.2,"previousClose":3259.95,"priceChange":11.25}';

        // Do something
        $commandTester->execute([
            'symbol' => 'AMZN',
            'region' => 'US'
        ]);

        // Make assertions
        $repo = $this->entityManager->getRepository(Stock::class);

        /** @var Stock $stock */
        $stock = $repo->findOneBy(['symbol' => 'AMZN']);

        $this->assertSame('USD', $stock->getCurrency());
        $this->assertSame('NasdaqGS', $stock->getExchangeName());
        $this->assertSame('AMZN', $stock->getSymbol());
        $this->assertSame('Amazon.com, Inc.', $stock->getShortName());
        $this->assertSame('US', $stock->getRegion());
        $this->assertGreaterThan('0', $stock->getPreviousClose());
        $this->assertGreaterThan('0', $stock->getPrice());
        $this->assertStringContainsString('Amazon.com, Inc. has been saved / updated.',$commandTester->getDisplay());
    }

    /** @test */
    public function non_200_status_code_responses_are_handled_correctly()
    {
        // Set up
        $application = new Application(self::$kernel);

        //Command -> symfony console make:command
        $command = $application->find('app:refresh-stock-profile');

        $commandTester = new CommandTester($command);

        // non-200 response
        FakeYahooFinanceApiClient::$statusCode = 500;

        FakeYahooFinanceApiClient::$content = 'Finance API client - ERROR';

        // Do something
        $commandStatus = $commandTester->execute([
            'symbol' => 'AMZN',
            'region' => 'US'
        ]);

        $repo = $this->entityManager->getRepository(Stock::class);

        $stockRecordCount = $repo->createQueryBuilder('stock')
            ->select('count(stock.id)')
            ->getQuery()
            ->getSingleScalarResult();

        // Make assertions
        $this->assertEquals(1, $commandStatus);
        $this->assertEquals(0, $stockRecordCount);
        $this->assertStringContainsString('Finance API client - ERROR',$commandTester->getDisplay());
    }

}