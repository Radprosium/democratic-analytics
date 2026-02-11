<?php

namespace App\Service\Graph;

use App\Dto\Graph\GraphIngestionBatchInput;
use App\Dto\Graph\OrganizationNodeInput;
use App\Dto\Graph\PersonNodeInput;
use App\Dto\Graph\RelationshipInput;
use App\Dto\Graph\RelationshipType;
use App\Entity\PoliticalPerson;

final class PoliticalPersonGraphMapper
{
    public function map(PoliticalPerson $person): GraphIngestionBatchInput
    {
        $personId = $person->getId()?->toRfc4122();
        if (null === $personId) {
            throw new \RuntimeException('Political person must have an ID before ingestion.');
        }

        $role = $person->getRole()?->value;
        if (null === $role) {
            throw new \RuntimeException('Political person must have a role before ingestion.');
        }

        $personInput = new PersonNodeInput(
            personId: $personId,
            name: (string) $person->getName(),
            slug: (string) $person->getSlug(),
            role: $role,
            wikipediaUrl: $person->getWikipediaUrl(),
            summary: $person->getSummary(),
            updatedAt: $person->getUpdatedAt() ?? new \DateTimeImmutable(),
        );

        $affiliation = $person->getAffiliation();
        if (null === $affiliation || '' === trim($affiliation)) {
            return new GraphIngestionBatchInput($personInput);
        }

        $orgId = 'org:'.$this->slugify($affiliation);
        $organizationInput = new OrganizationNodeInput(
            orgId: $orgId,
            name: $affiliation,
            updatedAt: $personInput->updatedAt,
        );

        $relationshipInput = new RelationshipInput(
            fromId: $personId,
            toId: $orgId,
            type: RelationshipType::MemberOf,
            source: 'wikipedia',
            confidence: 0.6,
            updatedAt: $personInput->updatedAt,
        );

        return new GraphIngestionBatchInput($personInput, $organizationInput, $relationshipInput);
    }

    private function slugify(string $text): string
    {
        $text = transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', $text);
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        $text = trim((string) $text, '-');

        return '' === $text ? 'unknown' : $text;
    }
}