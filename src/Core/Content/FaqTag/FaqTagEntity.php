<?php

declare(strict_types=1);

namespace fKuenzel\Faq\Core\Content\FaqTag;

use fKuenzel\Faq\Core\Content\Faq\FaqCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

final class FaqTagEntity extends Entity
{
    use EntityIdTrait;

    protected ?string $name = null;

    protected ?FaqCollection $faqs = null;

    protected ?FaqTagTranslationCollection $translations = null;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getFaqs(): ?FaqCollection
    {
        return $this->faqs;
    }

    public function setFaqs(?FaqCollection $faqs): void
    {
        $this->faqs = $faqs;
    }

    public function getTranslations(): ?FaqTagTranslationCollection
    {
        return $this->translations;
    }

    public function setTranslations(?FaqTagTranslationCollection $translations): void
    {
        $this->translations = $translations;
    }
}
