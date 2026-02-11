<?php

namespace App\Dto\Graph;

final readonly class PersonNodeInput
{
    public string $personId;
    public string $name;
    public string $slug;
    public string $role;
    public ?string $wikipediaUrl;
    public ?string $summary;
    public ?\DateTimeImmutable $updatedAt;

    public function __construct(
        string $personId,
        string $name,
        string $slug,
        string $role,
        ?string $wikipediaUrl = null,
        ?string $summary = null,
        ?\DateTimeImmutable $updatedAt = null,
    ) {
        $this->personId = self::requireNonEmpty($personId, 'personId');
        $this->name = self::requireNonEmpty($name, 'name');
        $this->slug = self::requireNonEmpty($slug, 'slug');
        $this->role = self::requireNonEmpty($role, 'role');
        $this->wikipediaUrl = null !== $wikipediaUrl && '' === trim($wikipediaUrl) ? null : $wikipediaUrl;
        $this->summary = null !== $summary && '' === trim($summary) ? null : $summary;
        $this->updatedAt = $updatedAt;
    }

    private static function requireNonEmpty(string $value, string $field): string
    {
        $trimmed = trim($value);
        if ('' === $trimmed) {
            throw new \InvalidArgumentException(sprintf('PersonNodeInput %s cannot be empty.', $field));
        }

        return $trimmed;
    }
}