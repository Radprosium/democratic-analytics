<?php

namespace App\Dto\Graph;

final readonly class OrganizationNodeInput
{
    public string $orgId;
    public string $name;
    public ?\DateTimeImmutable $updatedAt;

    public function __construct(
        string $orgId,
        string $name,
        ?\DateTimeImmutable $updatedAt = null,
    ) {
        $this->orgId = self::requireNonEmpty($orgId, 'orgId');
        $this->name = self::requireNonEmpty($name, 'name');
        $this->updatedAt = $updatedAt;
    }

    private static function requireNonEmpty(string $value, string $field): string
    {
        $trimmed = trim($value);
        if ('' === $trimmed) {
            throw new \InvalidArgumentException(sprintf('OrganizationNodeInput %s cannot be empty.', $field));
        }

        return $trimmed;
    }
}