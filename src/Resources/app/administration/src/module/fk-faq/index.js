import deDE from './snippet/de-DE.json';
import enGB from './snippet/en-GB.json';

const { Module } = Shopware;

Shopware.Component.register('fk-faq-list', () => import('./page/fk-faq-list'));
Shopware.Component.register('fk-faq-detail', () => import('./page/fk-faq-detail'));

Module.register('fk-faq', {
    type: 'plugin',
    name: 'fk-faq',
    title: 'fk-faq.general.mainMenuItemGeneral',
    description: 'fk-faq.general.descriptionTextModule',
    version: '1.0.0',
    targetVersion: '1.0.0',
    color: '#57D9A3',
    icon: 'regular-comments',
    entity: 'fk_faq',

    snippets: {
        'de-DE': deDE,
        'en-GB': enGB,
    },

    routes: {
        index: {
            component: 'fk-faq-list',
            path: 'index',
        },
        create: {
            component: 'fk-faq-detail',
            path: 'create',
            meta: {
                parentPath: 'fk.faq.index',
            },
        },
        detail: {
            component: 'fk-faq-detail',
            path: 'detail/:id',
            meta: {
                parentPath: 'fk.faq.index',
            },
            props: {
                default(route) {
                    return {
                        faqId: route.params.id.toLowerCase(),
                    };
                },
            },
        },
    },

    navigation: [
        {
            id: 'fk-faq',
            label: 'fk-faq.general.mainMenuItemGeneral',
            path: 'fk.faq.index',
            parent: 'sw-content',
            color: '#57D9A3',
            icon: 'regular-comments',
            position: 100,
        },
    ],
});
