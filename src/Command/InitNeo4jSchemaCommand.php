<?php

namespace App\Command;

use App\Service\Graph\Neo4jClient;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCommand(
    name: 'app:neo4j:init-schema',
    description: 'Initialize Neo4j schema constraints and indexes',
)]
final class InitNeo4jSchemaCommand extends Command
{
    public function __construct(
        private readonly Neo4jClient $neo4jClient,
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('path', null, InputOption::VALUE_OPTIONAL, 'Schema file path', 'config/neo4j/schema.cypher')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Print statements without executing');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $path = (string) $input->getOption('path');
        $schemaPath = str_starts_with($path, '/') ? $path : $this->projectDir.'/'.$path;

        if (!is_file($schemaPath)) {
            $io->error(sprintf('Schema file not found: %s', $schemaPath));

            return Command::FAILURE;
        }

        $contents = trim((string) file_get_contents($schemaPath));
        if ('' === $contents) {
            $io->warning('Schema file is empty. Nothing to do.');

            return Command::SUCCESS;
        }

        $lines = preg_split('/\r\n|\r|\n/', $contents);
        $lines = array_filter($lines, static function (string $line): bool {
            $trimmed = ltrim($line);

            return '' === $trimmed || !str_starts_with($trimmed, '//');
        });
        $contents = trim(implode("\n", $lines));

        $statements = preg_split('/;\s*\n/', $contents);
        $statements = array_values(array_filter(array_map('trim', $statements)));

        if ($input->getOption('dry-run')) {
            $io->title('Neo4j schema statements (dry run)');
            foreach ($statements as $statement) {
                $io->writeln($statement.';');
            }

            return Command::SUCCESS;
        }

        $io->title('Applying Neo4j schema');

        foreach ($statements as $statement) {
            $this->neo4jClient->run($statement);
        }

        $io->success('Neo4j schema applied successfully.');

        return Command::SUCCESS;
    }
}