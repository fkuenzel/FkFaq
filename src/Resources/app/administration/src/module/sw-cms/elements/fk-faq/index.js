Shopware.Component.register('sw-cms-el-fk-faq', () => import('./component'));
Shopware.Component.register('sw-cms-el-config-fk-faq', () => import('./config'));
Shopware.Component.register('sw-cms-el-preview-fk-faq', () => import('./preview'));

/**
 * FAQ-Element fuer die Erlebniswelten.
 *
 * Die Konfiguration liegt in cms_slot.config, ausgewertet wird sie serverseitig
 * vom FaqCmsElementResolver. Der Name muss mit dessen getType() uebereinstimmen.
 */
Shopware.Service('cmsService').registerCmsElement({
    name: 'fk-faq',
    label: 'fk-faq-cms.element.label',
    component: 'sw-cms-el-fk-faq',
    configComponent: 'sw-cms-el-config-fk-faq',
    previewComponent: 'sw-cms-el-preview-fk-faq',
    defaultConfig: {
        faqIds: {
            source: 'static',
            value: [],
        },
        categoryIds: {
            source: 'static',
            value: [],
        },
        tagIds: {
            source: 'static',
            value: [],
        },
        headline: {
            source: 'static',
            value: '',
        },
        showHeadline: {
            source: 'static',
            value: true,
        },
    },
});
