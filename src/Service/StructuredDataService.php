<?php

declare(strict_types=1);

namespace fKuenzel\Faq\Service;

use fKuenzel\Faq\Core\Content\Faq\FaqCollection;
use fKuenzel\Faq\Core\Content\Faq\FaqEntity;

/**
 * Erzeugt JSON-LD nach schema.org/FAQPage.
 *
 * Hinweis zur Wirkung: Google zeigt FAQ-Rich-Results seit August 2023 nur noch
 * fuer Behoerden- und Gesundheitsseiten an. Fuer einen Shop bringt das Markup
 * derzeit keine zusaetzliche Darstellung in der Google-Suche. Andere
 * Suchmaschinen und maschinelle Leser werten es weiterhin aus, deshalb bleibt
 * die Funktion erhalten - standardmaessig aber abgeschaltet.
 */
final class StructuredDataService
{
    private const SCHEMA_CONTEXT = 'https://schema.org';

    private const MAX_ANSWER_LENGTH = 5000;

    public function __construct(
        private readonly ConfigService $configService,
    ) {
    }

    public function isSchemaGenerationEnabled(?string $salesChannelId = null): bool
    {
        return $this->configService->isGenerateSchema($salesChannelId);
    }

    /**
     * @return array<string, mixed> leeres Array, wenn kein verwertbarer Eintrag uebrig bleibt
     */
    public function generateFaqPageSchema(FaqCollection $faqs, ?string $pageUrl = null): array
    {
        $mainEntity = [];

        foreach ($faqs as $faq) {
            if (!$faq instanceof FaqEntity) {
                continue;
            }

            $entry = $this->buildQuestionEntry($faq);

            if ($entry !== null) {
                $mainEntity[] = $entry;
            }
        }

        if ($mainEntity === []) {
            return [];
        }

        $schema = [
            '@context' => self::SCHEMA_CONTEXT,
            '@type' => 'FAQPage',
            'mainEntity' => $mainEntity,
        ];

        if ($pageUrl !== null && $pageUrl !== '') {
            $schema['url'] = $pageUrl;
        }

        return $schema;
    }

    /**
     * @param array<string, mixed> $schema
     */
    public function renderSchemaTag(array $schema): string
    {
        if ($schema === []) {
            return '';
        }

        try {
            // Die HEX-Flags verhindern, dass ein </script> im Antworttext den
            // Skriptkontext aufbricht.
            $jsonLd = json_encode(
                $schema,
                JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
                | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT,
            );
        } catch (\JsonException) {
            return '';
        }

        return sprintf('<script type="application/ld+json">%s</script>', $jsonLd);
    }

    /**
     * @param array<string, mixed> $schema
     */
    public function isValidSchema(array $schema): bool
    {
        if (!isset($schema['@context'], $schema['@type'], $schema['mainEntity'])) {
            return false;
        }

        if ($schema['@type'] !== 'FAQPage') {
            return false;
        }

        if (!\is_array($schema['mainEntity']) || $schema['mainEntity'] === []) {
            return false;
        }

        foreach ($schema['mainEntity'] as $entry) {
            if (!\is_array($entry) || !isset($entry['@type'], $entry['name'], $entry['acceptedAnswer'])) {
                return false;
            }

            if ($entry['@type'] !== 'Question') {
                return false;
            }

            if (!\is_array($entry['acceptedAnswer'])
                || !isset($entry['acceptedAnswer']['@type'], $entry['acceptedAnswer']['text'])
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function buildQuestionEntry(FaqEntity $faq): ?array
    {
        $question = $this->normalizeText($faq->getTitle());
        $answer = $this->normalizeText($faq->getAnswer());

        if ($question === null || $answer === null) {
            return null;
        }

        if (\mb_strlen($answer) > self::MAX_ANSWER_LENGTH) {
            $answer = \mb_substr($answer, 0, self::MAX_ANSWER_LENGTH) . '...';
        }

        return [
            '@type' => 'Question',
            'name' => $question,
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => $answer,
            ],
        ];
    }

    /**
     * Die Entity traegt bereits die Werte der aktuellen Sprache; ueber
     * getTranslations() zu gehen wuerde eine beliebige Uebersetzung greifen.
     */
    private function normalizeText(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $stripped = strip_tags($value);
        $normalized = preg_replace('/\s+/', ' ', $stripped);
        $text = trim($normalized ?? $stripped);

        return $text === '' ? null : $text;
    }
}
