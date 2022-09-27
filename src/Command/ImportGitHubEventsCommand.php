<?php

declare(strict_types=1);

namespace App\Command;

use App\GHArchive\Client\GHEventsFacade;
use Symfony\Component\Console\Command\Command;
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
            ->setDescription('Import GH events');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Yeah');
        $this->client->saveDailyEvents(new \DateTimeImmutable());

        return self::SUCCESS;
    }
}
