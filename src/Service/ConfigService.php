<?php

declare(strict_types=1);

namespace fKuenzel\Faq\Service;

use Shopware\Core\System\SystemConfig\SystemConfigService;

/**
 * Zugriff auf die Plugin-Konfiguration.
 *
 * Die Rueckfallwerte spiegeln die defaultValue-Angaben aus der config.xml.
 * Normalerweise schreibt Shopware diese beim Installieren in die
 * system_config; fehlt der Eintrag trotzdem - etwa weil das Plugin vor einer
 * Feldaenderung installiert wurde - verhaelt sich das Plugin so, wie die
 * Konfigurationsmaske es anzeigt.
 */
class ConfigService
{
    private const CONFIG_PREFIX = 'FkFaq.config.';

    public const POSITION_TAB = 'as_tab';
    public const POSITION_UNDER_DESCRIPTION = 'under_description';

    public const SORT_DEFAULT = 'alphabetical_asc';

    public function __construct(
        private readonly SystemConfigService $systemConfigService,
    ) {
    }

    public function isAutoAttachToProducts(?string $salesChannelId = null): bool
    {
        $value = $this->systemConfigService->get(self::CONFIG_PREFIX . 'autoAttachToProducts', $salesChannelId);

        // Ein bewusst abgeschaltetes Feld liefert false und darf nicht auf den
        // Standard zurueckfallen - nur ein fehlender Eintrag tut das.
        return $value === null ? true : (bool) $value;
    }

    public function getAttachmentPosition(?string $salesChannelId = null): string
    {
        $value = $this->systemConfigService->get(self::CONFIG_PREFIX . 'attachmentPosition', $salesChannelId);

        return \is_string($value) && $value !== '' ? $value : self::POSITION_TAB;
    }

    public function isGenerateSchema(?string $salesChannelId = null): bool
    {
        $value = $this->systemConfigService->get(self::CONFIG_PREFIX . 'generateSchema', $salesChannelId);

        return (bool) $value;
    }

    public function getDefaultSortOrder(?string $salesChannelId = null): string
    {
        $value = $this->systemConfigService->get(self::CONFIG_PREFIX . 'defaultSortOrder', $salesChannelId);

        return \is_string($value) && $value !== '' ? $value : self::SORT_DEFAULT;
    }
}
