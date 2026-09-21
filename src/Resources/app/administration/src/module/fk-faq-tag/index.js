import deDE from './snippet/de-DE.json';
import enGB from './snippet/en-GB.json';

const { Module } = Shopware;

Shopware.Component.register('fk-faq-tag-list', () => import('./page/fk-faq-tag-list'));
Shopware.Component.register('fk-faq-tag-detail', () => import('./page/fk-faq-tag-detail'));

Module.register('fk-faq-tag', {
    type: 'plugin',
    name: 'fk-faq-tag',
    title: 'fk-faq-tag.general.mainMenuItemGeneral',
    description: 'fk-faq-tag.general.descriptionTextModule',
    version: '1.0.0',
    targetVersion: '1.0.0',
    color: '#57D9A3',
    icon: 'regular-flag',
    entity: 'fk_faq_tag',

    snippets: {
        'de-DE': deDE,
        'en-GB': enGB,
    },

    routes: {
        index: {
            component: 'fk-faq-tag-list',
            path: 'index',
        },
        create: {
            component: 'fk-faq-tag-detail',
            path: 'create',
            meta: {
                parentPath: 'fk.faq.tag.index',
            },
        },
        detail: {
            component: 'fk-faq-tag-detail',
            path: 'detail/:id',
            meta: {
                parentPath: 'fk.faq.tag.index',
            },
            props: {
                default(route) {
                    return {
                        tagId: route.params.id.toLowerCase(),
                    };
                },
            },
        },
    },

    navigation: [
        {
            id: 'fk-faq-tag',
            label: 'fk-faq-tag.general.mainMenuItemGeneral',
            path: 'fk.faq.tag.index',
            parent: 'sw-content',
            color: '#57D9A3',
            icon: 'regular-flag',
            position: 120,
        },
    ],
});
