import deDE from './snippet/de-DE.json';
import enGB from './snippet/en-GB.json';

const { Module } = Shopware;

Shopware.Component.register('fk-faq-category-list', () => import('./page/fk-faq-category-list'));
Shopware.Component.register('fk-faq-category-detail', () => import('./page/fk-faq-category-detail'));

Module.register('fk-faq-category', {
    type: 'plugin',
    name: 'fk-faq-category',
    title: 'fk-faq-category.general.mainMenuItemGeneral',
    description: 'fk-faq-category.general.descriptionTextModule',
    version: '1.0.0',
    targetVersion: '1.0.0',
    color: '#57D9A3',
    icon: 'regular-bars-square',
    entity: 'fk_faq_category',

    snippets: {
        'de-DE': deDE,
        'en-GB': enGB,
    },

    routes: {
        index: {
            component: 'fk-faq-category-list',
            path: 'index',
        },
        create: {
            component: 'fk-faq-category-detail',
            path: 'create',
            meta: {
                parentPath: 'fk.faq.category.index',
            },
        },
        detail: {
            component: 'fk-faq-category-detail',
            path: 'detail/:id',
            meta: {
                parentPath: 'fk.faq.category.index',
            },
            props: {
                default(route) {
                    return {
                        categoryId: route.params.id.toLowerCase(),
                    };
                },
            },
        },
    },

    navigation: [
        {
            id: 'fk-faq-category',
            label: 'fk-faq-category.general.mainMenuItemGeneral',
            path: 'fk.faq.category.index',
            parent: 'sw-content',
            color: '#57D9A3',
            icon: 'regular-bars-square',
            position: 110,
        },
    ],
});
