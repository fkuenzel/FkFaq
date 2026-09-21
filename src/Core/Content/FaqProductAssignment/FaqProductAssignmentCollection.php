<?php

declare(strict_types=1);

namespace fKuenzel\Faq\Core\Content\FaqProductAssignment;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @extends EntityCollection<FaqProductAssignmentEntity>
 */
final class FaqProductAssignmentCollection extends EntityCollection
{
    /**
     * @return array<string>
     */
    public function getFaqIds(): array
    {
        return $this->fmap(static fn (FaqProductAssignmentEntity $assignment) => $assignment->getFkFaqId());
    }

    public function getApiAlias(): string
    {
        return 'fk_faq_product_assignment_collection';
    }

    protected function getExpectedClass(): string
    {
        return FaqProductAssignmentEntity::class;
    }
}
