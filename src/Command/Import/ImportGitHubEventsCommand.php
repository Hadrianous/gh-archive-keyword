<?php

declare(strict_types=1);

namespace App\Command\Import;

use App\GHArchive\Client\GHEventsFacade;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This command must import GitHub events.
 * You can add the parameters and code you want in this command to meet the need.
 */
class ImportGitHubEventsCommand extends Command
{
    protected static $defaultName = 'app:import:github-events';
    private GHEventsFacade $client;

    public function __construct(GHEventsFacade $client)
    {
        parent::__construct();
        $this->client = $client;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Import GH events')
            ->addArgument('date', InputArgument::REQUIRED, 'Date with format Y-m-d');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $date = $input->getArgument('date');
        $dateTime = \DateTimeImmutable::createFromFormat('Y-m-d', $date);

        $output->writeln("Start import for date $date");
        $progressBar = new ProgressBar($output, 23);
        $i = 0;
        while ($i++ < 24) {
            $this->client->saveHourlyEventsFromArchive($dateTime, $i);
            $progressBar->advance();
        }
        $progressBar->finish();

        $output->writeln('Import finished');
        return self::SUCCESS;
    }
}
