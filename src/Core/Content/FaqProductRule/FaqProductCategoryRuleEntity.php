<?php

declare(strict_types=1);

namespace fKuenzel\Faq\Core\Content\FaqProductRule;

use fKuenzel\Faq\Core\Content\FaqCategory\FaqCategoryEntity;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

final class FaqProductCategoryRuleEntity extends Entity
{
    use EntityIdTrait;

    protected ?string $productId = null;

    protected ?string $productVersionId = null;

    protected ?string $fkFaqCategoryId = null;

    protected ?ProductEntity $product = null;

    protected ?FaqCategoryEntity $faqCategory = null;

    public function getProductId(): ?string
    {
        return $this->productId;
    }

    public function setProductId(?string $productId): void
    {
        $this->productId = $productId;
    }

    public function getProductVersionId(): ?string
    {
        return $this->productVersionId;
    }

    public function setProductVersionId(?string $productVersionId): void
    {
        $this->productVersionId = $productVersionId;
    }

    public function getFkFaqCategoryId(): ?string
    {
        return $this->fkFaqCategoryId;
    }

    public function setFkFaqCategoryId(?string $fkFaqCategoryId): void
    {
        $this->fkFaqCategoryId = $fkFaqCategoryId;
    }

    public function getProduct(): ?ProductEntity
    {
        return $this->product;
    }

    public function setProduct(?ProductEntity $product): void
    {
        $this->product = $product;
    }

    public function getFaqCategory(): ?FaqCategoryEntity
    {
        return $this->faqCategory;
    }

    public function setFaqCategory(?FaqCategoryEntity $faqCategory): void
    {
        $this->faqCategory = $faqCategory;
    }
}
