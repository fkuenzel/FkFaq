<?php

declare(strict_types=1);

namespace fKuenzel\Faq\Tests\Unit\Storefront\Struct;

use fKuenzel\Faq\Core\Content\Faq\FaqCollection;
use fKuenzel\Faq\Core\Content\Faq\FaqEntity;
use fKuenzel\Faq\Storefront\Struct\FaqPageExtension;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Uuid\Uuid;

class FaqPageExtensionTest extends TestCase
{
    #[Test]
    public function itReportsAnEmptyCollection(): void
    {
        $extension = new FaqPageExtension(new FaqCollection(), FaqPageExtension::POSITION_TAB);

        static::assertFalse($extension->hasFaqs());
        static::assertSame(0, $extension->getFaqs()->count());
    }

    #[Test]
    public function itDistinguishesTheTwoPositions(): void
    {
        $tab = new FaqPageExtension($this->collection(), FaqPageExtension::POSITION_TAB);

        static::assertTrue($tab->hasFaqs());
        static::assertTrue($tab->isTab());
        static::assertFalse($tab->isUnderDescription());

        $below = new FaqPageExtension($this->collection(), FaqPageExtension::POSITION_UNDER_DESCRIPTION);

        static::assertFalse($below->isTab());
        static::assertTrue($below->isUnderDescription());
    }

    #[Test]
    public function itTreatsAnUnknownPositionAsNeither(): void
    {
        $extension = new FaqPageExtension($this->collection(), 'irgendwas');

        static::assertFalse($extension->isTab());
        static::assertFalse($extension->isUnderDescription());
    }

    private function collection(): FaqCollection
    {
        $faq = new FaqEntity();
        $faq->setId(Uuid::randomHex());
        $faq->setTitle('Frage');
        $faq->setAnswer('Antwort');

        return new FaqCollection([$faq]);
    }
}
