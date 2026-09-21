<?php

declare(strict_types=1);

namespace fKuenzel\Faq\Tests\Unit\Service;

use fKuenzel\Faq\Core\Content\Faq\FaqCollection;
use fKuenzel\Faq\Core\Content\Faq\FaqEntity;
use fKuenzel\Faq\Service\ConfigService;
use fKuenzel\Faq\Service\FaqStorefrontService;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\FieldVisibility;
use Shopware\Core\Test\Stub\DataAbstractionLayer\StaticEntityRepository;

class FaqStorefrontServiceTest extends TestCase
{
    private FaqStorefrontService $service;
    private StaticEntityRepository $faqRepository;
    private StaticEntityRepository $productAssignmentRepository;
    private StaticEntityRepository $productCategoryRuleRepository;
    private StaticEntityRepository $productTagRuleRepository;
    private ConfigService $configService;

    protected function setUp(): void
    {
        $this->faqRepository = new StaticEntityRepository([]);
        $this->productAssignmentRepository = new StaticEntityRepository([]);
        $this->productCategoryRuleRepository = new StaticEntityRepository([]);
        $this->productTagRuleRepository = new StaticEntityRepository([]);
        $this->configService = $this->createMock(ConfigService::class);

        $this->configService->method('getDefaultSortOrder')
            ->willReturn(FaqStorefrontService::SORT_ALPHABETICAL_ASC);

        $this->service = new FaqStorefrontService(
            $this->faqRepository,
            $this->productAssignmentRepository,
            $this->productCategoryRuleRepository,
            $this->productTagRuleRepository,
            $this->configService,
        );
    }

    public function testReturnEmptyCollectionWhenNoFaqIdsProvided(): void
    {
        $faqs = $this->service->getFaqs([], [], [], Context::createDefaultContext());

        $this->assertInstanceOf(FaqCollection::class, $faqs);
        $this->assertCount(0, $faqs);
    }

    public function testReturnManuallyAssignedFaqsInOrder(): void
    {
        $faqIds = ['faq-1', 'faq-2', 'faq-3'];
        $faqEntities = $this->createFaqEntities($faqIds);
        $collection = new FaqCollection($faqEntities);

        // Nur ein search()-Aufruf: loadActiveFaqs() ueber faqRepository.
        $this->faqRepository->addSearch($collection);

        $faqs = $this->service->getFaqs($faqIds, [], [], Context::createDefaultContext());

        $this->assertCount(3, $faqs);
        $this->assertEquals('faq-1', $faqs->first()->getId());
    }

    public function testRemovesDuplicatesFromDynamicAndManualAssignments(): void
    {
        $manualIds = ['faq-1', 'faq-2'];
        $dynamicIds = ['faq-2', 'faq-3'];

        $faqEntities = $this->createFaqEntities(['faq-1', 'faq-2', 'faq-3']);
        $collection = new FaqCollection($faqEntities);

        // Reihenfolge der Aufrufe in FaqStorefrontService::resolve():
        // zuerst searchIds() (getRuleBasedFaqIds), danach search() (loadActiveFaqs).
        // StaticEntityRepository bedient beide aus derselben Warteschlange.
        $this->faqRepository->addSearch($dynamicIds, $collection);

        $faqs = $this->service->getFaqs($manualIds, ['cat-1'], [], Context::createDefaultContext());

        $this->assertCount(3, $faqs);
        $this->assertEquals('faq-1', $faqs->first()->getId());
    }

    public function testSortsDynamicFaqsInAlphabeticalAscOrder(): void
    {
        $faqEntities = $this->createFaqEntitiesWithTitles([
            ['id' => 'faq-1', 'title' => 'Zebra'],
            ['id' => 'faq-2', 'title' => 'Apple'],
            ['id' => 'faq-3', 'title' => 'Banana'],
        ]);

        $collection = new FaqCollection($faqEntities);

        $this->faqRepository->addSearch(['faq-1', 'faq-2', 'faq-3'], $collection);

        $faqs = $this->service->getFaqs([], ['cat-1'], [], Context::createDefaultContext(), null, FaqStorefrontService::SORT_ALPHABETICAL_ASC);

        $titles = array_values(array_map(fn($faq) => $faq->getTitle(), $faqs->getElements()));
        $this->assertEquals(['Apple', 'Banana', 'Zebra'], $titles);
    }

    public function testSortsDynamicFaqsInAlphabeticalDescOrder(): void
    {
        $faqEntities = $this->createFaqEntitiesWithTitles([
            ['id' => 'faq-1', 'title' => 'Apple'],
            ['id' => 'faq-2', 'title' => 'Zebra'],
            ['id' => 'faq-3', 'title' => 'Banana'],
        ]);

        $collection = new FaqCollection($faqEntities);

        $this->faqRepository->addSearch(['faq-1', 'faq-2', 'faq-3'], $collection);

        $faqs = $this->service->getFaqs([], ['cat-1'], [], Context::createDefaultContext(), null, FaqStorefrontService::SORT_ALPHABETICAL_DESC);

        $titles = array_values(array_map(fn($faq) => $faq->getTitle(), $faqs->getElements()));
        $this->assertEquals(['Zebra', 'Banana', 'Apple'], $titles);
    }

    public function testSortsDynamicFaqsByCreatedDateAscending(): void
    {
        $now = new \DateTime();
        $oneHourAgo = (clone $now)->sub(new \DateInterval('PT1H'));
        $twoHoursAgo = (clone $now)->sub(new \DateInterval('PT2H'));

        $faqEntities = $this->createFaqEntitiesWithDates([
            ['id' => 'faq-1', 'title' => 'Recent', 'createdAt' => $now],
            ['id' => 'faq-2', 'title' => 'Old', 'createdAt' => $twoHoursAgo],
            ['id' => 'faq-3', 'title' => 'Older', 'createdAt' => $oneHourAgo],
        ]);

        $collection = new FaqCollection($faqEntities);

        $this->faqRepository->addSearch(['faq-1', 'faq-2', 'faq-3'], $collection);

        $faqs = $this->service->getFaqs([], ['cat-1'], [], Context::createDefaultContext(), null, FaqStorefrontService::SORT_CREATED_ASC);

        $titles = array_values(array_map(fn($faq) => $faq->getTitle(), $faqs->getElements()));
        $this->assertEquals(['Old', 'Older', 'Recent'], $titles);
    }

    public function testSortsDynamicFaqsByCreatedDateDescending(): void
    {
        $now = new \DateTime();
        $oneHourAgo = (clone $now)->sub(new \DateInterval('PT1H'));
        $twoHoursAgo = (clone $now)->sub(new \DateInterval('PT2H'));

        $faqEntities = $this->createFaqEntitiesWithDates([
            ['id' => 'faq-1', 'title' => 'Recent', 'createdAt' => $now],
            ['id' => 'faq-2', 'title' => 'Old', 'createdAt' => $twoHoursAgo],
            ['id' => 'faq-3', 'title' => 'Older', 'createdAt' => $oneHourAgo],
        ]);

        $collection = new FaqCollection($faqEntities);

        $this->faqRepository->addSearch(['faq-1', 'faq-2', 'faq-3'], $collection);

        $faqs = $this->service->getFaqs([], ['cat-1'], [], Context::createDefaultContext(), null, FaqStorefrontService::SORT_CREATED_DESC);

        $titles = array_values(array_map(fn($faq) => $faq->getTitle(), $faqs->getElements()));
        $this->assertEquals(['Recent', 'Older', 'Old'], $titles);
    }

    public function testFallsBackToConfiguredSortOrderWhenInvalid(): void
    {
        $faqEntities = $this->createFaqEntitiesWithTitles([
            ['id' => 'faq-1', 'title' => 'Zebra'],
            ['id' => 'faq-2', 'title' => 'Apple'],
        ]);

        $collection = new FaqCollection($faqEntities);

        $this->faqRepository->addSearch(['faq-1', 'faq-2'], $collection);

        $this->configService->expects($this->once())
            ->method('getDefaultSortOrder')
            ->willReturn(FaqStorefrontService::SORT_ALPHABETICAL_ASC);

        $faqs = $this->service->getFaqs([], ['cat-1'], [], Context::createDefaultContext(), null, 'invalid_sort');

        $titles = array_values(array_map(fn($faq) => $faq->getTitle(), $faqs->getElements()));
        $this->assertEquals(['Apple', 'Zebra'], $titles);
    }

    public function testGetFaqsByCategoryFiltersCorrectly(): void
    {
        $categoryId = 'cat-123';
        $faqEntities = $this->createFaqEntities(['faq-1', 'faq-2']);

        $collection = new FaqCollection($faqEntities);
        $this->faqRepository->addSearch($collection);

        $faqs = $this->service->getFaqsByCategory($categoryId, Context::createDefaultContext());

        $this->assertCount(2, $faqs);
    }

    public function testGetFaqsByTagsFiltersCorrectly(): void
    {
        $tagIds = ['tag-1', 'tag-2'];
        $faqEntities = $this->createFaqEntities(['faq-1', 'faq-2']);

        $collection = new FaqCollection($faqEntities);
        $this->faqRepository->addSearch($collection);

        $faqs = $this->service->getFaqsByTags($tagIds, Context::createDefaultContext());

        $this->assertCount(2, $faqs);
    }

    public function testReturnEmptyCollectionWhenSearchTermEmpty(): void
    {
        $faqs = $this->service->searchFaqs('', Context::createDefaultContext());

        $this->assertCount(0, $faqs);
    }

    public function testSearchFaqsExecutesSearch(): void
    {
        $searchTerm = 'test';
        $faqEntities = $this->createFaqEntities(['faq-1']);

        $collection = new FaqCollection($faqEntities);
        $this->faqRepository->addSearch($collection);

        $faqs = $this->service->searchFaqs($searchTerm, Context::createDefaultContext());

        $this->assertCount(1, $faqs);
    }

    /**
     * @param array<string> $ids
     * @return array<FaqEntity>
     */
    private function createFaqEntities(array $ids): array
    {
        return array_map(
            fn($id) => $this->createFaqEntity($id, 'FAQ Title'),
            $ids
        );
    }

    /**
     * @param array<array{id: string, title: string}> $data
     * @return array<FaqEntity>
     */
    private function createFaqEntitiesWithTitles(array $data): array
    {
        return array_map(
            fn($item) => $this->createFaqEntity($item['id'], $item['title']),
            $data
        );
    }

    /**
     * @param array<array{id: string, title: string, createdAt: \DateTime}> $data
     * @return array<FaqEntity>
     */
    private function createFaqEntitiesWithDates(array $data): array
    {
        return array_map(
            fn($item) => $this->createFaqEntity($item['id'], $item['title'], $item['createdAt']),
            $data
        );
    }

    private function createFaqEntity(string $id, string $title, ?\DateTime $createdAt = null): FaqEntity
    {
        $faq = new FaqEntity();
        $faq->setId($id);
        $faq->setTitle($title);
        $faq->setAnswer('Test Answer');
        $faq->setActive(true);

        // StaticEntityRepository wird ohne EntityDefinition konstruiert, daher
        // lautet der von ihr verwendete Entity-Name intern "mock" (siehe
        // StaticEntityRepository::getDummyEntityName()). EntitySearchResult prueft
        // seit Shopware 6.7.14 per assert(), dass dieser Name mit der API-Alias der
        // ersten Entity in der Collection uebereinstimmt. Manuell gebaute Entities
        // wie hier haben ohne diesen Aufruf keinen internen Entity-Namen und fallen
        // sonst auf einen aus dem Klassennamen abgeleiteten Fallback zurueck ("faq"
        // statt "fk_faq"), was die Assertion fehlschlagen laesst.
        $faq->internalSetEntityData('mock', new FieldVisibility([]));

        if ($createdAt) {
            $faq->setCreatedAt($createdAt);
        }

        return $faq;
    }
}
