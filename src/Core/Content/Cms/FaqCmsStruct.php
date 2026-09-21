<?php

declare(strict_types=1);

namespace fKuenzel\Faq\Core\Content\Cms;

use fKuenzel\Faq\Core\Content\Faq\FaqCollection;
use Shopware\Core\Framework\Struct\Struct;

/**
 * Ergebnis des FAQ-CMS-Elements, im Template als element.data verfuegbar.
 */
final class FaqCmsStruct extends Struct
{
    protected FaqCollection $faqs;

    protected ?string $headline = null;

    protected bool $showHeadline = true;

    public function __construct()
    {
        $this->faqs = new FaqCollection();
    }

    public function getFaqs(): FaqCollection
    {
        return $this->faqs;
    }

    public function setFaqs(FaqCollection $faqs): void
    {
        $this->faqs = $faqs;
    }

    public function hasFaqs(): bool
    {
        return $this->faqs->count() > 0;
    }

    public function getHeadline(): ?string
    {
        return $this->headline;
    }

    public function setHeadline(?string $headline): void
    {
        $this->headline = $headline;
    }

    public function getShowHeadline(): bool
    {
        return $this->showHeadline;
    }

    public function setShowHeadline(bool $showHeadline): void
    {
        $this->showHeadline = $showHeadline;
    }

    public function getApiAlias(): string
    {
        return 'fk_faq_cms';
    }
}
