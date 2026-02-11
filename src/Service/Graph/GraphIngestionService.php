<?php

namespace App\Service\Graph;

use App\Dto\Graph\GraphIngestionBatchInput;
use App\Dto\Graph\OrganizationNodeInput;
use App\Dto\Graph\PersonNodeInput;
use App\Dto\Graph\RelationshipInput;

final readonly class GraphIngestionService
{
    public function __construct(
        private Neo4jClient $neo4jClient,
    ) {
    }

    public function ingestBatch(GraphIngestionBatchInput $batch): void
    {
        $this->upsertPerson($batch->person);

        if (null !== $batch->organization) {
            $this->upsertOrganization($batch->organization);
        }

        if (null !== $batch->relationship) {
            $this->upsertRelationship($batch->relationship);
        }
    }

    private function upsertPerson(PersonNodeInput $person): void
    {
        $updatedAt = ($person->updatedAt ?? new \DateTimeImmutable())->format(DATE_ATOM);

        $this->neo4jClient->run(
            <<<'CYPHER'
MERGE (p:Person {personId: $personId})
SET p.name = $name,
    p.slug = $slug,
    p.role = $role,
    p.wikipediaUrl = $wikipediaUrl,
    p.summary = $summary,
    p.updatedAt = datetime($updatedAt)
CYPHER,
            [
                'personId' => $person->personId,
                'name' => $person->name,
                'slug' => $person->slug,
                'role' => $person->role,
                'wikipediaUrl' => $person->wikipediaUrl,
                'summary' => $person->summary,
                'updatedAt' => $updatedAt,
            ]
        );
    }

    private function upsertOrganization(OrganizationNodeInput $organization): void
    {
        $updatedAt = ($organization->updatedAt ?? new \DateTimeImmutable())->format(DATE_ATOM);

        $this->neo4jClient->run(
            <<<'CYPHER'
MERGE (o:Organization {orgId: $orgId})
SET o.name = $name,
    o.updatedAt = datetime($updatedAt)
CYPHER,
            [
                'orgId' => $organization->orgId,
                'name' => $organization->name,
                'updatedAt' => $updatedAt,
            ]
        );
    }

    private function upsertRelationship(RelationshipInput $relationship): void
    {
        $updatedAt = ($relationship->updatedAt ?? new \DateTimeImmutable())->format(DATE_ATOM);
        $type = $relationship->type->cypherLabel();

        $cypher = <<<CYPHER
MATCH (from:Person {personId: \$fromId})
MATCH (to:Organization {orgId: \$toId})
MERGE (from)-[r:$type]->(to)
SET r.source = \$source,
    r.confidence = \$confidence,
    r.updatedAt = datetime(\$updatedAt)
CYPHER;

        $this->neo4jClient->run($cypher, [
            'fromId' => $relationship->fromId,
            'toId' => $relationship->toId,
            'source' => $relationship->source,
            'confidence' => $relationship->confidence,
            'updatedAt' => $updatedAt,
        ]);
    }
}