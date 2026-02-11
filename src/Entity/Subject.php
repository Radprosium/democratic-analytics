<?php

namespace App\Entity;

use App\Enum\SubjectCategory;
use App\Enum\SubjectStatus;
use App\Repository\SubjectRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: SubjectRepository::class)]
#[ORM\Table(name: 'subject')]
#[ORM\HasLifecycleCallbacks]
class Subject
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?Uuid $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $slug = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $summary = null;

    #[ORM\Column(length: 50, enumType: SubjectCategory::class)]
    private ?SubjectCategory $category = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $importance = null;

    #[ORM\Column(length: 20, enumType: SubjectStatus::class)]
    private SubjectStatus $status = SubjectStatus::Active;

    /** @var array<string> */
    #[ORM\Column(type: Types::JSON)]
    private array $sources = [];

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $lastRetrievedAt = null;

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getSummary(): ?string
    {
        return $this->summary;
    }

    public function setSummary(string $summary): static
    {
        $this->summary = $summary;

        return $this;
    }

    public function getCategory(): ?SubjectCategory
    {
        return $this->category;
    }

    public function setCategory(SubjectCategory $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getImportance(): ?int
    {
        return $this->importance;
    }

    public function setImportance(int $importance): static
    {
        $this->importance = max(1, min(10, $importance));

        return $this;
    }

    public function getStatus(): SubjectStatus
    {
        return $this->status;
    }

    public function setStatus(SubjectStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    /** @return array<string> */
    public function getSources(): array
    {
        return $this->sources;
    }

    /** @param array<string> $sources */
    public function setSources(array $sources): static
    {
        $this->sources = $sources;

        return $this;
    }

    public function addSource(string $source): static
    {
        if (!\in_array($source, $this->sources, true)) {
            $this->sources[] = $source;
        }

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getLastRetrievedAt(): ?\DateTimeImmutable
    {
        return $this->lastRetrievedAt;
    }

    public function setLastRetrievedAt(?\DateTimeImmutable $lastRetrievedAt): static
    {
        $this->lastRetrievedAt = $lastRetrievedAt;

        return $this;
    }
}
