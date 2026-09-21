<?php

declare(strict_types=1);

namespace fKuenzel\Faq\Tests\Unit\Service;

use fKuenzel\Faq\Core\Content\Faq\FaqCollection;
use fKuenzel\Faq\Core\Content\Faq\FaqEntity;
use fKuenzel\Faq\Service\ConfigService;
use fKuenzel\Faq\Service\StructuredDataService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Uuid\Uuid;

class StructuredDataServiceTest extends TestCase
{
    #[Test]
    public function itReturnsAnEmptySchemaForAnEmptyCollection(): void
    {
        $service = new StructuredDataService($this->createMock(ConfigService::class));

        static::assertSame([], $service->generateFaqPageSchema(new FaqCollection()));
    }

    #[Test]
    public function itBuildsAFaqPageSchema(): void
    {
        $service = new StructuredDataService($this->createMock(ConfigService::class));

        $schema = $service->generateFaqPageSchema(new FaqCollection([
            $this->faq('Wie lange dauert der Versand?', '<p>In der Regel <b>zwei</b> Werktage.</p>'),
        ]));

        static::assertSame('https://schema.org', $schema['@context']);
        static::assertSame('FAQPage', $schema['@type']);
        static::assertCount(1, $schema['mainEntity']);
        static::assertSame('Question', $schema['mainEntity'][0]['@type']);
        static::assertSame('Wie lange dauert der Versand?', $schema['mainEntity'][0]['name']);
        static::assertSame('In der Regel zwei Werktage.', $schema['mainEntity'][0]['acceptedAnswer']['text']);
    }

    #[Test]
    public function itSkipsEntriesWithoutAnAnswer(): void
    {
        $service = new StructuredDataService($this->createMock(ConfigService::class));

        $schema = $service->generateFaqPageSchema(new FaqCollection([
            $this->faq('Frage ohne Antwort', ''),
        ]));

        static::assertSame([], $schema);
    }

    #[Test]
    public function itValidatesACompleteSchema(): void
    {
        $service = new StructuredDataService($this->createMock(ConfigService::class));

        $schema = $service->generateFaqPageSchema(new FaqCollection([
            $this->faq('Frage', 'Antwort'),
        ]));

        static::assertTrue($service->isValidSchema($schema));
        static::assertFalse($service->isValidSchema([]));
        static::assertFalse($service->isValidSchema(['@context' => 'https://schema.org', '@type' => 'Article', 'mainEntity' => []]));
    }

    #[Test]
    public function itEscapesClosingScriptTagsInTheRenderedTag(): void
    {
        $service = new StructuredDataService($this->createMock(ConfigService::class));

        $schema = $service->generateFaqPageSchema(new FaqCollection([
            $this->faq('Frage', 'Antwort </script><script>alert(1)</script>'),
        ]));

        $tag = $service->renderSchemaTag($schema);

        static::assertStringNotContainsString('</script><script>', $tag);
        static::assertStringEndsWith('</script>', $tag);
        static::assertStringStartsWith('<script type="application/ld+json">', $tag);
    }

    #[Test]
    public function itReturnsAnEmptyTagForAnEmptySchema(): void
    {
        $service = new StructuredDataService($this->createMock(ConfigService::class));

        static::assertSame('', $service->renderSchemaTag([]));
    }

    private function faq(string $title, string $answer): FaqEntity
    {
        $faq = new FaqEntity();
        $faq->setId(Uuid::randomHex());
        $faq->setTitle($title);
        $faq->setAnswer($answer);

        return $faq;
    }
}
