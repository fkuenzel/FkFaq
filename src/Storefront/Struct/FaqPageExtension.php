<?php

declare(strict_types=1);

namespace fKuenzel\Faq\Storefront\Struct;

use fKuenzel\Faq\Core\Content\Faq\FaqCollection;
use Shopware\Core\Framework\Struct\Struct;

/**
 * Traegt die zum Produkt gehoerenden FAQs an die Seite.
 *
 * Page-Extension statt customFields: customFields schickt die Administration
 * beim Speichern des Produkts zurueck, Entity-Objekte darin fuehren zu
 * Schreibfehlern. Extensions an der Page sind genau fuer diesen Zweck da.
 */
final class FaqPageExtension extends Struct
{
    public const EXTENSION_NAME = 'fkFaq';

    public const POSITION_TAB = 'as_tab';
    public const POSITION_UNDER_DESCRIPTION = 'under_description';

    public function __construct(
        private readonly FaqCollection $faqs,
        private readonly string $position,
    ) {
    }

    public function getFaqs(): FaqCollection
    {
        return $this->faqs;
    }

    public function getPosition(): string
    {
        return $this->position;
    }

    public function hasFaqs(): bool
    {
        return $this->faqs->count() > 0;
    }

    public function isTab(): bool
    {
        return $this->position === self::POSITION_TAB;
    }

    public function isUnderDescription(): bool
    {
        return $this->position === self::POSITION_UNDER_DESCRIPTION;
    }

    public function getApiAlias(): string
    {
        return 'fk_faq_page_extension';
    }
}
