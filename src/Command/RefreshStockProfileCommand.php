<?php

namespace App\Command;

use App\Entity\Stock;
use App\Http\FinanceApiClientInterface;
use App\Http\YahooFinanceApiClient;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Serializer\SerializerInterface;

class RefreshStockProfileCommand extends Command
{
    protected static $defaultName = 'app:refresh-stock-profile';
    private EntityManagerInterface $entityManager;
    private FinanceApiClientInterface $financeApiClient;
    private SerializerInterface $serializer;

    public function __construct(EntityManagerInterface    $entityManager,
                                FinanceApiClientInterface $financeApiClient,
                                SerializerInterface       $serializer)
    {

        parent::__construct();
        $this->entityManager = $entityManager;
        $this->financeApiClient = $financeApiClient;
        $this->serializer = $serializer;
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
        $stockProfile = $this->financeApiClient->fetchStockProfile(
            $input->getArgument('symbol'), $input->getArgument('region')
        );

        if ($stockProfile->getStatusCode() !== 200) {
            return Command::FAILURE;
        }

        $stock = $this->serializer->deserialize($stockProfile->getContent(), Stock::class, 'json');

        $this->entityManager->persist($stock);
        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}
