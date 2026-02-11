<?php

namespace App\Service;

use App\Entity\Subject;
use App\Enum\SubjectCategory;
use App\Enum\SubjectStatus;
use App\Repository\SubjectRepository;
use Psr\Log\LoggerInterface;
use Symfony\AI\Agent\AgentInterface;
use Symfony\AI\Platform\Message\Message;
use Symfony\AI\Platform\Message\MessageBag;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class NewsRetrievalService
{
    public function __construct(
        #[Autowire(service: 'ai.agent.news_retriever')]
        private AgentInterface $newsRetrieverAgent,
        private SubjectRepository $subjectRepository,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * Retrieve the latest political news and create/update subjects.
     *
     * @return Subject[] The subjects that were created or updated
     */
    public function retrieveLatestNews(): array
    {
        $this->logger->info('Starting news retrieval...');

        $messages = new MessageBag(
            Message::ofUser('What are the most important political subjects being discussed today? Identify the key political debates, policy decisions, and significant events. Respond with a JSON array of subjects.'),
        );

        try {
            $response = $this->newsRetrieverAgent->call($messages);
            $content = $response->getContent();
        } catch (\Throwable $e) {
            $this->logger->error('AI agent call failed: '.$e->getMessage());
            throw new \RuntimeException('Failed to retrieve news from AI platform: '.$e->getMessage(), 0, $e);
        }

        $this->logger->info('AI response received, parsing subjects...');

        $subjectsData = $this->parseResponse($content);

        if (0 === \count($subjectsData)) {
            $this->logger->warning('No subjects found in AI response.');

            return [];
        }

        $subjects = [];
        foreach ($subjectsData as $data) {
            try {
                $subject = $this->createOrUpdateSubject($data);
                $subjects[] = $subject;
                $this->logger->info(\sprintf('Subject processed: "%s"', $subject->getTitle()));
            } catch (\Throwable $e) {
                $this->logger->error(\sprintf('Failed to process subject: %s', $e->getMessage()));
            }
        }

        return $subjects;
    }

    /**
     * @return array<array<string, mixed>>
     */
    private function parseResponse(string $content): array
    {
        // Strip possible markdown code block wrapping
        $content = trim($content);
        if (str_starts_with($content, '```')) {
            $content = preg_replace('/^```(?:json)?\s*/i', '', $content);
            $content = preg_replace('/\s*```\s*$/', '', $content);
        }

        $decoded = \json_decode($content, true);

        if (!\is_array($decoded)) {
            $this->logger->error('Failed to decode AI response as JSON', ['content' => mb_substr($content, 0, 500)]);

            return [];
        }

        // Handle both flat array and nested {"subjects": [...]} format
        if (isset($decoded['subjects']) && \is_array($decoded['subjects'])) {
            return $decoded['subjects'];
        }

        // Check if it's a flat array of subject objects
        if (isset($decoded[0]) && \is_array($decoded[0])) {
            return $decoded;
        }

        return [];
    }

    /**
     * @param array<string, mixed> $data
     */
    private function createOrUpdateSubject(array $data): Subject
    {
        $title = $data['title'] ?? throw new \InvalidArgumentException('Subject must have a title');
        $slug = $this->slugify($title);

        // Check for existing subject
        $subject = $this->subjectRepository->findBySlug($slug);

        if (null !== $subject) {
            // Update existing
            $this->logger->info(\sprintf('Updating existing subject: "%s"', $title));
            $subject->setSummary($data['summary'] ?? $subject->getSummary());
            if (isset($data['importance'])) {
                $subject->setImportance((int) $data['importance']);
            }
            if (isset($data['sources']) && \is_array($data['sources'])) {
                foreach ($data['sources'] as $source) {
                    $subject->addSource($source);
                }
            }
            $subject->setLastRetrievedAt(new \DateTimeImmutable());
        } else {
            // Create new
            $subject = new Subject();
            $subject->setTitle($title);
            $subject->setSlug($slug);
            $subject->setSummary($data['summary'] ?? '');
            $subject->setCategory(
                SubjectCategory::tryFrom($data['category'] ?? 'other') ?? SubjectCategory::Other
            );
            $subject->setImportance(max(1, min(10, (int) ($data['importance'] ?? 5))));
            $subject->setStatus(SubjectStatus::Active);
            $subject->setSources(\is_array($data['sources'] ?? null) ? $data['sources'] : []);
            $subject->setLastRetrievedAt(new \DateTimeImmutable());
        }

        $this->subjectRepository->save($subject);

        return $subject;
    }

    private function slugify(string $text): string
    {
        $text = transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', $text);
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        $text = trim($text, '-');

        return $text ?: 'untitled';
    }
}
