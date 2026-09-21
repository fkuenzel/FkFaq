import deDE from './snippet/de-DE.json';
import enGB from './snippet/en-GB.json';
import swProductDetailOverride from './component/sw-product-detail/sw-product-detail.html.twig';

const { Module } = Shopware;
const { Criteria } = Shopware.Data;

Shopware.Component.register('fk-faq-product-tab', () => import('./view/fk-faq-product-tab'));

/**
 * Ergaenzt die Produktdetailseite um den Reiter "FAQ" und laedt die
 * FAQ-Assoziationen mit, die die ProductExtension an product haengt.
 *
 * Ohne das Nachladen waere product.extensions.fkFaq* beim Speichern nicht
 * vorhanden; der Changeset-Generator der Administration braucht den
 * Ausgangszustand, um Aenderungen und Loeschungen zu erkennen.
 */
Shopware.Component.override('sw-product-detail', {
    template: swProductDetailOverride,

    computed: {
        productCriteria() {
            const criteria = this.$super('productCriteria');

            criteria
                .getAssociation('fkFaqAssignments')
                .addSorting(Criteria.sort('position', 'ASC'))
                .addAssociation('faq');

            criteria.addAssociation('fkFaqCategoryRules');
            criteria.addAssociation('fkFaqTagRules');

            return criteria;
        },
    },
});

Module.register('fk-faq-product', {
    type: 'plugin',
    name: 'fk-faq-product',
    title: 'fk-faq-product.general.mainMenuItemGeneral',
    description: 'fk-faq-product.general.descriptionTextModule',
    version: '1.0.0',
    targetVersion: '1.0.0',

    snippets: {
        'de-DE': deDE,
        'en-GB': enGB,
    },

    /**
     * Haengt eine zusaetzliche Kindroute an die bestehende Produktdetailseite.
     *
     * Der Pfad muss exakt dem Muster der Core-Kindrouten folgen. Die
     * module.factory setzt diese als "<Elternpfad>/<Kindpfad>" zusammen, der
     * Elternpfad ist "/sw/product/detail/:id?" - mit optionalem Parameter.
     * Ein abweichendes Muster wie ":id" ohne Fragezeichen erzeugt einen
     * eigenen Matcher-Eintrag statt einer echten Kindroute.
     */
    routeMiddleware(next, currentRoute) {
        if (currentRoute.name === 'sw.product.detail') {
            currentRoute.children.push({
                name: 'sw.product.detail.fkFaq',
                path: '/sw/product/detail/:id?/fk-faq',
                component: 'fk-faq-product-tab',
                meta: {
                    parentPath: 'sw.product.index',
                    privilege: 'product.viewer',
                },
            });
        }

        next(currentRoute);
    },
});
