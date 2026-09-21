<?php

declare(strict_types=1);

namespace fKuenzel\Faq\Core\Content\FaqProductAssignment;

use fKuenzel\Faq\Core\Content\Faq\FaqEntity;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

final class FaqProductAssignmentEntity extends Entity
{
    use EntityIdTrait;

    protected ?string $productId = null;

    protected ?string $productVersionId = null;

    protected ?string $fkFaqId = null;

    protected int $position = 0;

    protected ?ProductEntity $product = null;

    protected ?FaqEntity $faq = null;

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

    public function getFkFaqId(): ?string
    {
        return $this->fkFaqId;
    }

    public function setFkFaqId(?string $fkFaqId): void
    {
        $this->fkFaqId = $fkFaqId;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    public function getProduct(): ?ProductEntity
    {
        return $this->product;
    }

    public function setProduct(?ProductEntity $product): void
    {
        $this->product = $product;
    }

    public function getFaq(): ?FaqEntity
    {
        return $this->faq;
    }

    public function setFaq(?FaqEntity $faq): void
    {
        $this->faq = $faq;
    }
}
