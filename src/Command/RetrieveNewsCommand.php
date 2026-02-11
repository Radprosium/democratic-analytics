<?php

namespace App\Command;

use App\Service\NewsRetrievalService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:retrieve-news',
    description: 'Retrieve the latest political news and create/update subjects via AI analysis',
)]
final class RetrieveNewsCommand extends Command
{
    public function __construct(
        private readonly NewsRetrievalService $newsRetrievalService,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Democratic Analytics — News Retrieval');
        $io->info('Querying Perplexity AI for today\'s political subjects...');

        try {
            $subjects = $this->newsRetrievalService->retrieveLatestNews();
        } catch (\Throwable $e) {
            $io->error('News retrieval failed: '.$e->getMessage());

            if ($output->isVerbose()) {
                $io->writeln($e->getTraceAsString());
            }

            return Command::FAILURE;
        }

        if (0 === \count($subjects)) {
            $io->warning('No subjects were identified. This may indicate an issue with the AI response.');

            return Command::SUCCESS;
        }

        $io->success(\sprintf('Successfully processed %d subject(s):', \count($subjects)));

        $rows = [];
        foreach ($subjects as $subject) {
            $rows[] = [
                $subject->getTitle(),
                $subject->getCategory()?->label() ?? 'N/A',
                $subject->getImportance(),
                $subject->getStatus()->label(),
            ];
        }

        $io->table(['Title', 'Category', 'Importance', 'Status'], $rows);

        return Command::SUCCESS;
    }
}
