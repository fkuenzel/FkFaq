<?php

declare(strict_types=1);

namespace fKuenzel\Faq\Core\Content\FaqTag;

use Shopware\Core\Framework\DataAbstractionLayer\TranslationEntity;

final class FaqTagTranslation extends TranslationEntity
{
    protected ?string $fkFaqTagId = null;

    protected ?string $name = null;

    protected ?FaqTagEntity $fkFaqTag = null;

    public function getFkFaqTagId(): ?string
    {
        return $this->fkFaqTagId;
    }

    public function setFkFaqTagId(?string $fkFaqTagId): void
    {
        $this->fkFaqTagId = $fkFaqTagId;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getFkFaqTag(): ?FaqTagEntity
    {
        return $this->fkFaqTag;
    }

    public function setFkFaqTag(?FaqTagEntity $fkFaqTag): void
    {
        $this->fkFaqTag = $fkFaqTag;
    }
}
