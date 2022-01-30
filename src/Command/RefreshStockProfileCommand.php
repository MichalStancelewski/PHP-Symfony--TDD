<?php

namespace App\Command;

use App\Entity\Stock;
use App\Http\YahooFinanceApiClient;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RefreshStockProfileCommand extends Command
{
    protected static $defaultName = 'app:refresh-stock-profile';
    private EntityManagerInterface $entityManager;
    private YahooFinanceApiClient $yahooFinanceApiClient;

    public function __construct(EntityManagerInterface $entityManager, YahooFinanceApiClient $yahooFinanceApiClient)
    {

        parent::__construct();
        $this->entityManager = $entityManager;
        $this->yahooFinanceApiClient = $yahooFinanceApiClient;
    }


    protected function configure(): void
    {
        $this
            ->setDescription('Retrieve a stock profile from Yahoo Finance API. Update the record in the DB')
            ->addArgument('symbol', InputArgument::REQUIRED, 'Stock symbol, e.g. AMZN for Amazon')
            ->addArgument('region', InputArgument::REQUIRED, 'The region of the company, e.g. US for United States');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $stockProfile = $this->yahooFinanceApiClient->fetchStockProfile(
            $input->getArgument('symbol'), $input->getArgument('region')
        );

        if ($stockProfile['statusCode'] !== 200) {

        }

        $stock = $this->serializer->deserialize($stockProfile['content'], Stock::class, 'json');

        /*$stock = new Stock();
        $stock->setSymbol($stockProfile->symbol);
        $stock->setShortName($stockProfile->shortName);
        $stock->setCurrency($stockProfile->currency);
        $stock->setExchangeName($stockProfile->exchangeName);
        $stock->setRegion($stockProfile->region);
        $stock->setPrice($stockProfile->price);
        $stock->setPreviousClose($stockProfile->previousClose);
        $priceChange = $stockProfile->price - $stockProfile->previousClose;
        $stock->setPriceChange($priceChange);
*/
        $this->entityManager->persist($stock);
        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}
