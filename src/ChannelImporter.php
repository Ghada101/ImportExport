<?php

namespace App;

use Doctrine\ORM\EntityManagerInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Importer\ImporterInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Importer\ImporterResult;
use FriendsOfSylius\SyliusImportExportPlugin\Importer\ImporterResultInterface;
use Sylius\Component\Channel\Model\Channel;
use Symfony\Component\Stopwatch\Stopwatch;
use Psr\Log\LoggerInterface;

class ChannelImporter implements ImporterInterface
{
    private $stopwatch;
    private $logger;
    private $entityManager;

    public function __construct(Stopwatch $stopwatch, LoggerInterface $logger , EntityManagerInterface $entityManager)
    {
        $this->stopwatch = $stopwatch;
        $this->logger = $logger;
        $this->entityManager = $entityManager;
    }

    public function import(string $fileName): ImporterResultInterface
    {
        try {
            $fileHandle = fopen($fileName, 'r');

            if (!$fileHandle) {
                throw new \Exception("Failed to open file: $fileName");
            }

            // Initialize any service or repository required for data manipulation
            $entityManager = $this->entityManager; // Injected dependency

            // Loop through each row of the CSV file
            while (($row = fgetcsv($fileHandle)) !== false) {
                // Process each row as needed
                // For example, assuming CSV columns are: id, name, description
                $id = $row[0];
                $name = $row[1];
                $description = $row[2];
                $code = $row[3];
                $hostname = $row[4];
                $color = $row[5];
                $baseCurrency= $row[6];

                // Map the data to your system's entities
                $channel = new Channel();
                $channel->setName($name);
                $channel->setDescription($description);
                $channel->setCode($code);
                $channel->setHostname($hostname);
                $channel->setColor($color);

                // Insert or update the data into your system
                $entityManager->persist($channel);
            }

            // Commit changes to the database
            $entityManager->flush();

            fclose($fileHandle);

            return new ImporterResult( );
        } catch (\Exception $e) {
            return new ImporterResult($e->getMessage());
        }
    }
}
