<?php

declare(strict_types=1);

namespace fKuenzel\Faq\Core\Content\Faq;

use fKuenzel\Faq\Core\Content\FaqCategory\FaqCategoryEntity;
use fKuenzel\Faq\Core\Content\FaqProductAssignment\FaqProductAssignmentCollection;
use fKuenzel\Faq\Core\Content\FaqTag\FaqTagCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

final class FaqEntity extends Entity
{
    use EntityIdTrait;

    protected ?string $title = null;

    protected ?string $answer = null;

    protected ?string $categoryId = null;

    protected bool $active = true;

    protected int $position = 0;

    protected ?FaqCategoryEntity $category = null;

    protected ?FaqTagCollection $tags = null;

    protected ?FaqTranslationCollection $translations = null;

    protected ?FaqProductAssignmentCollection $productAssignments = null;

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

    public function getCategoryId(): ?string
    {
        return $this->categoryId;
    }

    public function setCategoryId(?string $categoryId): void
    {
        $this->categoryId = $categoryId;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    public function getCategory(): ?FaqCategoryEntity
    {
        return $this->category;
    }

    public function setCategory(?FaqCategoryEntity $category): void
    {
        $this->category = $category;
    }

    public function getTags(): ?FaqTagCollection
    {
        return $this->tags;
    }

    public function setTags(?FaqTagCollection $tags): void
    {
        $this->tags = $tags;
    }

    public function getTranslations(): ?FaqTranslationCollection
    {
        return $this->translations;
    }

    public function setTranslations(?FaqTranslationCollection $translations): void
    {
        $this->translations = $translations;
    }

    public function getProductAssignments(): ?FaqProductAssignmentCollection
    {
        return $this->productAssignments;
    }

    public function setProductAssignments(?FaqProductAssignmentCollection $productAssignments): void
    {
        $this->productAssignments = $productAssignments;
    }
}
