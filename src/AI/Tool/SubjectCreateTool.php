<?php

namespace App\AI\Tool;

use App\Entity\Subject;
use App\Enum\SubjectCategory;
use App\Enum\SubjectStatus;
use App\Repository\SubjectRepository;
use Symfony\AI\Agent\Toolbox\Attribute\AsTool;

#[AsTool(
    name: 'subject_create',
    description: 'Create a new political subject in the database. Use this after verifying with subject_lookup that the subject does not already exist. Requires title, summary, category, importance, and optionally sources.',
)]
final readonly class SubjectCreateTool
{
    public function __construct(
        private SubjectRepository $subjectRepository,
    ) {
    }

    /**
     * @param string   $title      The title of the political subject
     * @param string   $summary    A comprehensive summary of the subject
     * @param string   $category   The category (domestic, european, international, economy, social, environment, justice, security, health, education, technology, culture, other)
     * @param int      $importance Importance rating from 1 to 10
     * @param string[] $sources    Array of source URLs
     */
    public function __invoke(
        string $title,
        string $summary,
        string $category = 'other',
        int $importance = 5,
        array $sources = [],
    ): string {
        $categoryEnum = SubjectCategory::tryFrom($category) ?? SubjectCategory::Other;

        $subject = new Subject();
        $subject->setTitle($title);
        $subject->setSlug($this->slugify($title));
        $subject->setSummary($summary);
        $subject->setCategory($categoryEnum);
        $subject->setImportance($importance);
        $subject->setStatus(SubjectStatus::Active);
        $subject->setSources($sources);
        $subject->setLastRetrievedAt(new \DateTimeImmutable());

        $this->subjectRepository->save($subject);

        return \json_encode([
            'success' => true,
            'id' => (string) $subject->getId(),
            'title' => $subject->getTitle(),
            'slug' => $subject->getSlug(),
            'message' => \sprintf('Subject "%s" created successfully.', $title),
        ]);
    }

    private function slugify(string $text): string
    {
        $text = transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', $text);
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        $text = trim($text, '-');

        return $text ?: 'untitled';
    }
}
