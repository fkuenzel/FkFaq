<?php

declare(strict_types=1);

namespace fKuenzel\Faq\Core\Content\Faq;

use Shopware\Core\Framework\DataAbstractionLayer\TranslationEntity;

final class FaqTranslation extends TranslationEntity
{
    protected ?string $fkFaqId = null;

    protected ?string $title = null;

    protected ?string $answer = null;

    protected ?FaqEntity $fkFaq = null;

    public function getFkFaqId(): ?string
    {
        return $this->fkFaqId;
    }

    public function setFkFaqId(?string $fkFaqId): void
    {
        $this->fkFaqId = $fkFaqId;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function getAnswer(): ?string
    {
        return $this->answer;
    }

    public function setAnswer(?string $answer): void
    {
        $this->answer = $answer;
    }

    public function getFkFaq(): ?FaqEntity
    {
        return $this->fkFaq;
    }

    public function setFkFaq(?FaqEntity $fkFaq): void
    {
        $this->fkFaq = $fkFaq;
    }
}
