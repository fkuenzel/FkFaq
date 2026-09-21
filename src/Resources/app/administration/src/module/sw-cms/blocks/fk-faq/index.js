Shopware.Component.register('sw-cms-block-fk-faq', () => import('./component'));
Shopware.Component.register('sw-cms-preview-fk-faq', () => import('./preview'));

/**
 * Block "FAQ" fuer die Erlebniswelten.
 *
 * Der Layout-Editor bietet zum Hinzufuegen Bloecke an, keine Elemente. Ein
 * Element allein ist nur ueber "Element austauschen" innerhalb eines
 * bestehenden Blocks erreichbar. Dieser Block traegt das FAQ-Element in
 * seinem einzigen Slot und macht es damit in der Blockauswahl sichtbar.
 */
Shopware.Service('cmsService').registerCmsBlock({
    name: 'fk-faq',
    label: 'fk-faq-cms.block.label',
    category: 'text',
    component: 'sw-cms-block-fk-faq',
    previewComponent: 'sw-cms-preview-fk-faq',
    defaultConfig: {
        marginBottom: '20px',
        marginTop: '20px',
        marginLeft: null,
        marginRight: null,
        sizingMode: 'boxed',
    },
    slots: {
        content: 'fk-faq',
    },
});
