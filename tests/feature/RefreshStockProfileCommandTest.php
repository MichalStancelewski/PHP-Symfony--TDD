<?php

namespace App\Tests\feature;

use App\Entity\Stock;
use App\Tests\DatabasePrimer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class RefreshStockProfileCommandTest extends KernelTestCase
{
    /** @var EntityManagerInterface */
    private $entityManager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        DatabasePrimer::prime($kernel);

        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->entityManager->close();
        $this->entityManager = null;
    }

    /** @test */
    public function the_refresh_stock_profile_command_behaves_correctly()
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
        $this->assertGreaterThan('50', $stock->getPreviousClose());
        $this->assertGreaterThan('50', $stock->getPrice());
    }


}