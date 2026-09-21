<?php

declare(strict_types=1);

namespace fKuenzel\Faq\Core\Content\FaqCategory;

use Shopware\Core\Framework\DataAbstractionLayer\TranslationEntity;

final class FaqCategoryTranslation extends TranslationEntity
{
    protected ?string $fkFaqCategoryId = null;

    protected ?string $name = null;

    protected ?FaqCategoryEntity $fkFaqCategory = null;

    public function getFkFaqCategoryId(): ?string
    {
        return $this->fkFaqCategoryId;
    }

    public function setFkFaqCategoryId(?string $fkFaqCategoryId): void
    {
        $this->fkFaqCategoryId = $fkFaqCategoryId;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getFkFaqCategory(): ?FaqCategoryEntity
    {
        return $this->fkFaqCategory;
    }

    public function setFkFaqCategory(?FaqCategoryEntity $fkFaqCategory): void
    {
        $this->fkFaqCategory = $fkFaqCategory;
    }
}
