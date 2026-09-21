<?php

declare(strict_types=1);

namespace fKuenzel\Faq\Storefront\Subscriber;

use fKuenzel\Faq\Core\Content\Faq\FaqCollection;
use fKuenzel\Faq\Service\ConfigService;
use fKuenzel\Faq\Service\FaqStorefrontService;
use fKuenzel\Faq\Storefront\Struct\FaqPageExtension;
use Shopware\Storefront\Page\Product\ProductPageLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Haengt die FAQs des Produkts an die Produktseite.
 *
 * Bewusst ProductPageLoadedEvent und nicht ProductEvents::PRODUCT_LOADED_EVENT:
 * Letzteres feuert bei jedem Laden eines Produkts, auch in der Administration,
 * bei Exporten und Importen.
 */
final class ProductPageSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly FaqStorefrontService $faqStorefrontService,
        private readonly ConfigService $configService,
    ) {
    }

    /**
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ProductPageLoadedEvent::class => 'onProductPageLoaded',
        ];
    }

    public function onProductPageLoaded(ProductPageLoadedEvent $event): void
    {
        $salesChannelId = $event->getSalesChannelContext()->getSalesChannelId();

        if (!$this->configService->isAutoAttachToProducts($salesChannelId)) {
            return;
        }

        $product = $event->getPage()->getProduct();

        $faqs = $this->resolveFaqs(
            $product->getId(),
            $product->getParentId(),
            $event,
            $salesChannelId,
        );

        if ($faqs->count() === 0) {
            return;
        }

        $event->getPage()->addExtension(
            FaqPageExtension::EXTENSION_NAME,
            new FaqPageExtension($faqs, $this->configService->getAttachmentPosition($salesChannelId)),
        );
    }

    /**
     * Varianten erben die Zuordnung des Hauptprodukts, solange an der Variante
     * selbst nichts hinterlegt ist. Ohne das muesste jede Variante einzeln
     * gepflegt werden.
     */
    private function resolveFaqs(
        string $productId,
        ?string $parentId,
        ProductPageLoadedEvent $event,
        string $salesChannelId,
    ): FaqCollection {
        $faqs = $this->faqStorefrontService->getFaqsForProduct(
            $productId,
            $event->getContext(),
            $salesChannelId,
        );

        if ($faqs->count() > 0 || $parentId === null) {
            return $faqs;
        }

        return $this->faqStorefrontService->getFaqsForProduct(
            $parentId,
            $event->getContext(),
            $salesChannelId,
        );
    }
}
