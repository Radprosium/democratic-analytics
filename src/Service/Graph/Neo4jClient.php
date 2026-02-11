<?php

namespace App\Service\Graph;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class Neo4jClient
{
    public function __construct(
        private HttpClientInterface $httpClient,
        #[Autowire('%env(NEO4J_HTTP_ENDPOINT)%')]
        private string $endpoint,
        #[Autowire('%env(NEO4J_USERNAME)%')]
        private string $username,
        #[Autowire('%env(NEO4J_PASSWORD)%')]
        private string $password,
        #[Autowire('%env(NEO4J_DATABASE)%')]
        private string $database,
    ) {
    }

    /**
     * @param array<string, mixed> $parameters
     * @return array<string, mixed>
     */
    public function run(string $cypher, array $parameters = []): array
    {
        $url = rtrim($this->endpoint, '/').'/db/'.$this->database.'/tx/commit';

        $payload = json_encode([
            'statements' => [
                [
                    'statement' => $cypher,
                    'parameters' => [] === $parameters ? new \stdClass() : $parameters,
                ],
            ],
        ], JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);

        $response = $this->httpClient->request('POST', $url, [
            'auth_basic' => [$this->username, $this->password],
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'body' => $payload,
        ]);

        $content = $response->getContent(false);
        $data = json_decode($content, true);

        if (!is_array($data)) {
            $status = $response->getStatusCode();
            $snippet = trim(mb_substr((string) $content, 0, 400));
            throw new \RuntimeException(sprintf('Unexpected Neo4j response (%d): %s', $status, $snippet ?: 'empty response'));
        }

        if (!empty($data['errors'])) {
            $message = $data['errors'][0]['message'] ?? 'Unknown Neo4j error.';
            throw new \RuntimeException($message);
        }

        return $data;
    }
}