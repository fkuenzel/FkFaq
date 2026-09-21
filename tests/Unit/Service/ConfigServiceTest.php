<?php

declare(strict_types=1);

namespace fKuenzel\Faq\Tests\Unit\Service;

use fKuenzel\Faq\Service\ConfigService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class ConfigServiceTest extends TestCase
{
    #[Test]
    public function itReadsTheConfiguredAttachmentPosition(): void
    {
        $service = new ConfigService($this->systemConfig([
            'FkFaq.config.attachmentPosition' => 'under_description',
        ]));

        static::assertSame('under_description', $service->getAttachmentPosition());
    }

    #[Test]
    public function itFallsBackToTheDefaultAttachmentPosition(): void
    {
        $service = new ConfigService($this->systemConfig([]));

        // Muss dem defaultValue der config.xml entsprechen.
        static::assertSame('as_tab', $service->getAttachmentPosition());
    }

    #[Test]
    public function itDistinguishesAnUnsetFlagFromOneTurnedOff(): void
    {
        $unset = new ConfigService($this->systemConfig([]));
        $off = new ConfigService($this->systemConfig([
            'FkFaq.config.autoAttachToProducts' => false,
        ]));

        static::assertTrue($unset->isAutoAttachToProducts());
        static::assertFalse($off->isAutoAttachToProducts());
    }

    #[Test]
    public function itFallsBackToTheDefaultSortOrder(): void
    {
        $service = new ConfigService($this->systemConfig([]));

        static::assertSame('alphabetical_asc', $service->getDefaultSortOrder());
    }

    /**
     * @return array<string, array{mixed, bool}>
     */
    public static function booleanValues(): array
    {
        return [
            'true' => [true, true],
            'false' => [false, false],
            'null' => [null, false],
            'string one' => ['1', true],
        ];
    }

    #[Test]
    #[DataProvider('booleanValues')]
    public function itCastsTheSchemaFlagToBool(mixed $stored, bool $expected): void
    {
        $service = new ConfigService($this->systemConfig([
            'FkFaq.config.generateSchema' => $stored,
        ]));

        static::assertSame($expected, $service->isGenerateSchema());
    }

    /**
     * @param array<string, mixed> $values
     */
    private function systemConfig(array $values): SystemConfigService
    {
        $systemConfig = $this->createMock(SystemConfigService::class);
        $systemConfig->method('get')->willReturnCallback(
            static fn (string $key, ?string $salesChannelId = null) => $values[$key] ?? null,
        );

        return $systemConfig;
    }
}
