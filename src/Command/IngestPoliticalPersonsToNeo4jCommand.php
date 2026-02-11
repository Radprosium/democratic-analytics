<?php

namespace App\Command;

use App\Enum\PoliticalRole;
use App\Entity\PoliticalPerson;
use App\Service\Graph\GraphIngestionService;
use App\Service\Graph\PoliticalPersonGraphMapper;
use App\Dto\Graph\GraphIngestionBatchInput;
use App\Dto\Graph\OrganizationNodeInput;
use App\Dto\Graph\PersonNodeInput;
use App\Dto\Graph\RelationshipInput;
use App\Dto\Graph\RelationshipType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:neo4j:ingest-political-persons',
    description: 'Ingest political persons from PostgreSQL into Neo4j',
)]
final class IngestPoliticalPersonsToNeo4jCommand extends Command
{
    public function __construct(
        private readonly ManagerRegistry $registry,
        private readonly PoliticalPersonGraphMapper $mapper,
        private readonly GraphIngestionService $graphIngestion,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('role', null, InputOption::VALUE_OPTIONAL, 'Limit ingestion to a specific role (president, prime_minister, minister, deputy, senator)')
            ->addOption('limit', null, InputOption::VALUE_OPTIONAL, 'Limit number of people to ingest (for testing)', 50)
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Validate DTOs without writing to Neo4j')
            ->addOption('sample', null, InputOption::VALUE_NONE, 'Use sample DTOs instead of database records');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Democratic Analytics — Neo4j Political Person Pipeline');

        $roleOption = $input->getOption('role');
        $limit = (int) $input->getOption('limit');
        $dryRun = (bool) $input->getOption('dry-run');

        $role = null;
        if (null !== $roleOption) {
            $role = PoliticalRole::tryFrom((string) $roleOption);
            if (null === $role) {
                $io->error('Unknown role. Use one of: president, prime_minister, minister, deputy, senator.');

                return Command::INVALID;
            }
        }

        $useSample = (bool) $input->getOption('sample') || !class_exists(PoliticalPerson::class);

        $batches = [];

        if ($useSample) {
            $io->note('Using sample DTOs (database entities not available).');
            $batches = $this->buildSampleBatches($limit);
        } else {
            $repository = $this->registry->getRepository(PoliticalPerson::class);
            $people = null !== $role
                ? $repository->findBy(['role' => $role], ['name' => 'ASC'], $limit)
                : $repository->findBy([], ['updatedAt' => 'DESC'], $limit);

            if (0 === count($people)) {
                $io->warning('No political persons available for ingestion. Run app:retrieve-political-persons first.');

                return Command::SUCCESS;
            }

            foreach ($people as $person) {
                $batches[] = $this->mapper->map($person);
            }
        }

        $success = 0;
        $failures = 0;

        foreach ($batches as $batch) {
            try {
                if (!$dryRun) {
                    $this->graphIngestion->ingestBatch($batch);
                }

                ++$success;
            } catch (\Throwable $e) {
                ++$failures;
                $io->warning(sprintf('Skipped record: %s', $e->getMessage()));
            }
        }

        if ($dryRun) {
            $io->success(sprintf('Validated %d record(s) (dry run).', $success));
        } else {
            $io->success(sprintf('Ingested %d record(s) into Neo4j.', $success));
        }

        if (0 < $failures) {
            $io->note(sprintf('%d record(s) failed validation or ingestion.', $failures));
        }

        return Command::SUCCESS;
    }

    /**
     * @return GraphIngestionBatchInput[]
     */
    private function buildSampleBatches(int $limit): array
    {
        $limit = max(1, $limit);
        $batches = [];

        for ($i = 1; $i <= $limit; ++$i) {
            $personId = Uuid::v4()->toRfc4122();
            $orgId = 'org:sample-party';
            $updatedAt = new \DateTimeImmutable();

            $person = new PersonNodeInput(
                personId: $personId,
                name: sprintf('Sample Person %d', $i),
                slug: sprintf('sample-person-%d', $i),
                role: 'minister',
                wikipediaUrl: null,
                summary: 'Sample DTO for pipeline validation.',
                updatedAt: $updatedAt,
            );

            $organization = new OrganizationNodeInput(
                orgId: $orgId,
                name: 'Sample Party',
                updatedAt: $updatedAt,
            );

            $relationship = new RelationshipInput(
                fromId: $personId,
                toId: $orgId,
                type: RelationshipType::MemberOf,
                source: 'sample',
                confidence: 0.5,
                updatedAt: $updatedAt,
            );

            $batches[] = new GraphIngestionBatchInput($person, $organization, $relationship);
        }

        return $batches;
    }
}