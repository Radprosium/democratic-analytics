<?php

namespace App\AI\Tool;

use App\Repository\SubjectRepository;
use Symfony\AI\Agent\Toolbox\Attribute\AsTool;

#[AsTool(
    name: 'subject_lookup',
    description: 'Search for existing political subjects in the database by keyword. Returns a list of matching subjects with their title, category, importance, and summary excerpt. Use this to check if a subject already exists before creating a new one.',
)]
final readonly class SubjectLookupTool
{
    public function __construct(
        private SubjectRepository $subjectRepository,
    ) {
    }

    /**
     * @param string $query The search keywords to look for in subject titles
     */
    public function __invoke(string $query): string
    {
        $subjects = $this->subjectRepository->searchByTitle($query);

        if (0 === \count($subjects)) {
            return \json_encode(['results' => [], 'message' => 'No matching subjects found.']);
        }

        $results = [];
        foreach ($subjects as $subject) {
            $results[] = [
                'id' => (string) $subject->getId(),
                'title' => $subject->getTitle(),
                'slug' => $subject->getSlug(),
                'category' => $subject->getCategory()?->value,
                'importance' => $subject->getImportance(),
                'status' => $subject->getStatus()->value,
                'summary_excerpt' => mb_substr($subject->getSummary() ?? '', 0, 200).'...',
                'updated_at' => $subject->getUpdatedAt()?->format('Y-m-d H:i'),
            ];
        }

        return \json_encode(['results' => $results, 'count' => \count($results)]);
    }
}
