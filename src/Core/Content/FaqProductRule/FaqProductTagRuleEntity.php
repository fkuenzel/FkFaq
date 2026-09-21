<?php

declare(strict_types=1);

namespace fKuenzel\Faq\Core\Content\FaqProductRule;

use fKuenzel\Faq\Core\Content\FaqTag\FaqTagEntity;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

final class FaqProductTagRuleEntity extends Entity
{
    use EntityIdTrait;

    protected ?string $productId = null;

    protected ?string $productVersionId = null;

    protected ?string $fkFaqTagId = null;

    protected ?ProductEntity $product = null;

    protected ?FaqTagEntity $faqTag = null;

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

    public function getFkFaqTagId(): ?string
    {
        return $this->fkFaqTagId;
    }

    public function setFkFaqTagId(?string $fkFaqTagId): void
    {
        $this->fkFaqTagId = $fkFaqTagId;
    }

    public function getProduct(): ?ProductEntity
    {
        return $this->product;
    }

    public function setProduct(?ProductEntity $product): void
    {
        $this->product = $product;
    }

    public function getFaqTag(): ?FaqTagEntity
    {
        return $this->faqTag;
    }

    public function setFaqTag(?FaqTagEntity $faqTag): void
    {
        $this->faqTag = $faqTag;
    }
}
