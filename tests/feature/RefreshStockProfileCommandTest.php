<?php

namespace App\Tests\feature;

use App\Entity\Stock;
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
    }


}