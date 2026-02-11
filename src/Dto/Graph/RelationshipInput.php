<?php

namespace App\Dto\Graph;

final readonly class RelationshipInput
{
    public string $fromId;
    public string $toId;
    public RelationshipType $type;
    public ?string $source;
    public ?float $confidence;
    public ?\DateTimeImmutable $updatedAt;

    public function __construct(
        string $fromId,
        string $toId,
        RelationshipType $type,
        ?string $source = null,
        ?float $confidence = null,
        ?\DateTimeImmutable $updatedAt = null,
    ) {
        $this->fromId = self::requireNonEmpty($fromId, 'fromId');
        $this->toId = self::requireNonEmpty($toId, 'toId');
        $this->type = $type;
        $this->source = null !== $source && '' === trim($source) ? null : $source;

        if (null !== $confidence && ($confidence < 0 || $confidence > 1)) {
            throw new \InvalidArgumentException('RelationshipInput confidence must be between 0 and 1.');
        }

        $this->confidence = $confidence;
        $this->updatedAt = $updatedAt;
    }

    private static function requireNonEmpty(string $value, string $field): string
    {
        $trimmed = trim($value);
        if ('' === $trimmed) {
            throw new \InvalidArgumentException(sprintf('RelationshipInput %s cannot be empty.', $field));
        }

        return $trimmed;
    }
}