<?php

namespace App\Dto\Graph;

final readonly class GraphIngestionBatchInput
{
    public function __construct(
        public PersonNodeInput $person,
        public ?OrganizationNodeInput $organization = null,
        public ?RelationshipInput $relationship = null,
    ) {
    }
}