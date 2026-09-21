<?php

declare(strict_types=1);

namespace fKuenzel\Faq\Tests\Unit\Storefront\Subscriber;

use fKuenzel\Faq\Core\Content\Faq\FaqCollection;
use fKuenzel\Faq\Core\Content\Faq\FaqEntity;
use fKuenzel\Faq\Service\ConfigService;
use fKuenzel\Faq\Service\FaqStorefrontService;
use fKuenzel\Faq\Storefront\Struct\FaqPageExtension;
use fKuenzel\Faq\Storefront\Subscriber\ProductPageSubscriber;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;
use Shopware\Storefront\Page\Product\ProductPageLoadedEvent;
use Shopware\Storefront\Page\Product\ProductPage;
use Symfony\Component\HttpFoundation\Request;

class ProductPageSubscriberTest extends TestCase
{
    private ProductPageSubscriber $subscriber;
    private FaqStorefrontService $faqService;
    private ConfigService $configService;

    protected function setUp(): void
    {
        $this->faqService = $this->createMock(FaqStorefrontService::class);
        $this->configService = $this->createMock(ConfigService::class);

        $this->subscriber = new ProductPageSubscriber(
            $this->faqService,
            $this->configService,
        );
    }

    public function testOnProductPageLoadedReturnsEarlyWhenAutoAttachDisabled(): void
    {
        $product = new SalesChannelProductEntity();
        $product->setId('prod-123');

        $page = new ProductPage();
        $page->setProduct($product);

        $salesChannelContext = $this->createMock(\Shopware\Core\System\SalesChannel\SalesChannelContext::class);
        $salesChannelContext->method('getContext')->willReturn(\Shopware\Core\Framework\Context::createDefaultContext());
        $salesChannelContext->method('getSalesChannelId')->willReturn('sales-channel-123');

        $request = $this->createMock(Request::class);
        $event = new ProductPageLoadedEvent($page, $salesChannelContext, $request);

        $this->configService->expects($this->once())
            ->method('isAutoAttachToProducts')
            ->with('sales-channel-123')
            ->willReturn(false);

        $this->faqService->expects($this->never())
            ->method('getFaqsForProduct');

        $this->subscriber->onProductPageLoaded($event);

        $this->assertFalse($page->hasExtension(FaqPageExtension::EXTENSION_NAME));
    }

    public function testOnProductPageLoadedAddsExtensionWhenFaqsAvailable(): void
    {
        $product = new SalesChannelProductEntity();
        $product->setId('prod-123');

        $page = new ProductPage();
        $page->setProduct($product);

        $salesChannelContext = $this->createMock(\Shopware\Core\System\SalesChannel\SalesChannelContext::class);
        $salesChannelContext->method('getContext')->willReturn(\Shopware\Core\Framework\Context::createDefaultContext());
        $salesChannelContext->method('getSalesChannelId')->willReturn('sales-channel-123');

        $request = $this->createMock(Request::class);
        $event = new ProductPageLoadedEvent($page, $salesChannelContext, $request);

        $faqEntity = new FaqEntity();
        $faqEntity->setId('faq-1');
        $faqEntity->setTitle('Häufig gestellte Frage');
        $faqEntity->setAnswer('Antwort zur Frage');
        $faqEntity->setActive(true);

        $faqCollection = new FaqCollection([$faqEntity]);

        $this->configService->expects($this->once())
            ->method('isAutoAttachToProducts')
            ->with('sales-channel-123')
            ->willReturn(true);

        $this->configService->expects($this->once())
            ->method('getAttachmentPosition')
            ->with('sales-channel-123')
            ->willReturn(ConfigService::POSITION_TAB);

        $this->faqService->expects($this->once())
            ->method('getFaqsForProduct')
            ->with('prod-123', $this->anything(), 'sales-channel-123')
            ->willReturn($faqCollection);

        $this->subscriber->onProductPageLoaded($event);

        $this->assertTrue($page->hasExtension(FaqPageExtension::EXTENSION_NAME));
    }

    public function testOnProductPageLoadedDoesNotAddExtensionWhenNoFaqsFound(): void
    {
        $product = new SalesChannelProductEntity();
        $product->setId('prod-123');

        $page = new ProductPage();
        $page->setProduct($product);

        $salesChannelContext = $this->createMock(\Shopware\Core\System\SalesChannel\SalesChannelContext::class);
        $salesChannelContext->method('getContext')->willReturn(\Shopware\Core\Framework\Context::createDefaultContext());
        $salesChannelContext->method('getSalesChannelId')->willReturn('sales-channel-123');

        $request = $this->createMock(Request::class);
        $event = new ProductPageLoadedEvent($page, $salesChannelContext, $request);

        $this->configService->expects($this->once())
            ->method('isAutoAttachToProducts')
            ->with('sales-channel-123')
            ->willReturn(true);

        $this->faqService->expects($this->once())
            ->method('getFaqsForProduct')
            ->with('prod-123', $this->anything(), 'sales-channel-123')
            ->willReturn(new FaqCollection());

        $this->subscriber->onProductPageLoaded($event);

        $this->assertFalse($page->hasExtension(FaqPageExtension::EXTENSION_NAME));
    }

    public function testOnProductPageLoadedRespectsAttachmentPosition(): void
    {
        $product = new SalesChannelProductEntity();
        $product->setId('prod-123');

        $page = new ProductPage();
        $page->setProduct($product);

        $salesChannelContext = $this->createMock(\Shopware\Core\System\SalesChannel\SalesChannelContext::class);
        $salesChannelContext->method('getContext')->willReturn(\Shopware\Core\Framework\Context::createDefaultContext());
        $salesChannelContext->method('getSalesChannelId')->willReturn('sales-channel-123');

        $request = $this->createMock(Request::class);
        $event = new ProductPageLoadedEvent($page, $salesChannelContext, $request);

        $faqEntity = new FaqEntity();
        $faqEntity->setId('faq-1');
        $faqEntity->setTitle('Häufig gestellte Frage');
        $faqEntity->setActive(true);

        $faqCollection = new FaqCollection([$faqEntity]);

        $this->configService->expects($this->once())
            ->method('isAutoAttachToProducts')
            ->with('sales-channel-123')
            ->willReturn(true);

        $this->configService->expects($this->once())
            ->method('getAttachmentPosition')
            ->with('sales-channel-123')
            ->willReturn(ConfigService::POSITION_UNDER_DESCRIPTION);

        $this->faqService->expects($this->once())
            ->method('getFaqsForProduct')
            ->with('prod-123', $this->anything(), 'sales-channel-123')
            ->willReturn($faqCollection);

        $this->subscriber->onProductPageLoaded($event);

        $this->assertTrue($page->hasExtension(FaqPageExtension::EXTENSION_NAME));
    }

    public function testOnProductPageLoadedFallsBackToParentProductWhenVariantHasNoFaqs(): void
    {
        $product = new SalesChannelProductEntity();
        $product->setId('variant-456');
        $product->setParentId('parent-123');

        $page = new ProductPage();
        $page->setProduct($product);

        $salesChannelContext = $this->createMock(\Shopware\Core\System\SalesChannel\SalesChannelContext::class);
        $salesChannelContext->method('getContext')->willReturn(\Shopware\Core\Framework\Context::createDefaultContext());
        $salesChannelContext->method('getSalesChannelId')->willReturn('sales-channel-123');

        $request = $this->createMock(Request::class);
        $event = new ProductPageLoadedEvent($page, $salesChannelContext, $request);

        $faqEntity = new FaqEntity();
        $faqEntity->setId('faq-1');
        $faqEntity->setTitle('Häufig gestellte Frage');
        $faqEntity->setActive(true);

        $this->configService->expects($this->once())
            ->method('isAutoAttachToProducts')
            ->with('sales-channel-123')
            ->willReturn(true);

        // Mock: First call for variant returns empty, second call for parent returns FAQs
        $this->faqService->expects($this->exactly(2))
            ->method('getFaqsForProduct')
            ->willReturnCallback(function($productId) use ($faqEntity) {
                if ($productId === 'variant-456') {
                    return new FaqCollection();  // Variant has no FAQs
                }
                if ($productId === 'parent-123') {
                    return new FaqCollection([$faqEntity]);  // Parent has FAQs
                }
                return new FaqCollection();
            });

        $this->subscriber->onProductPageLoaded($event);

        $this->assertTrue($page->hasExtension(FaqPageExtension::EXTENSION_NAME));
    }
}
