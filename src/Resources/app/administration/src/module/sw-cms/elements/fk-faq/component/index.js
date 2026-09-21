import template from './sw-cms-el-fk-faq.html.twig';
import './sw-cms-el-fk-faq.scss';

const { Mixin, Context } = Shopware;
const { Criteria } = Shopware.Data;

const LIMIT = 100;

/**
 * Darstellung des Elements im Layout-Editor.
 *
 * Zeigt dieselbe Zusammenstellung wie die Storefront, damit der Redakteur
 * beim Bauen sieht, was herauskommt. Die Aufloesung laeuft hier ueber die
 * Admin-API; massgeblich ist in der Storefront der FaqCmsElementResolver.
 */
export default {
    template,

    inject: ['repositoryFactory'],

    mixins: [
        Mixin.getByName('cms-element'),
    ],

    data() {
        return {
            faqs: [],
            isLoading: false,
        };
    },

    computed: {
        faqRepository() {
            return this.repositoryFactory.create('fk_faq');
        },

        faqIds() {
            return this.element?.config?.faqIds?.value ?? [];
        },

        categoryIds() {
            return this.element?.config?.categoryIds?.value ?? [];
        },

        tagIds() {
            return this.element?.config?.tagIds?.value ?? [];
        },

        showHeadline() {
            return this.element?.config?.showHeadline?.value ?? true;
        },

        headline() {
            const value = this.element?.config?.headline?.value;

            return value || this.$t('fk-faq-cms.element.defaultHeadline');
        },

        hasSelection() {
            return this.faqIds.length > 0
                || this.categoryIds.length > 0
                || this.tagIds.length > 0;
        },
    },

    watch: {
        'element.config': {
            deep: true,
            handler() {
                this.loadFaqs();
            },
        },
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.initElementConfig('fk-faq');
            this.loadFaqs();
        },

        async loadFaqs() {
            if (!this.hasSelection) {
                this.faqs = [];

                return;
            }

            this.isLoading = true;

            try {
                const [manual, byRule] = await Promise.all([
                    this.loadManualFaqs(),
                    this.loadRuleFaqs(),
                ]);

                // Manuell gewaehlte zuerst, danach die ueber Regeln ermittelten.
                this.faqs = [
                    ...manual,
                    ...byRule.filter((faq) => !this.faqIds.includes(faq.id)),
                ];
            } catch (error) {
                this.faqs = [];

                // eslint-disable-next-line no-console
                console.error('[FkFaq] Vorschau im Layout-Editor fehlgeschlagen', error);
            } finally {
                this.isLoading = false;
            }
        },

        async loadManualFaqs() {
            if (this.faqIds.length === 0) {
                return [];
            }

            const criteria = new Criteria(1, LIMIT);
            criteria.setIds(this.faqIds);

            const result = await this.faqRepository.search(criteria, Context.api);

            return this.faqIds
                .map((id) => [...result].find((faq) => faq.id === id))
                .filter(Boolean);
        },

        async loadRuleFaqs() {
            const conditions = [];

            if (this.categoryIds.length > 0) {
                conditions.push(Criteria.equalsAny('categoryId', this.categoryIds));
            }

            if (this.tagIds.length > 0) {
                conditions.push(Criteria.equalsAny('tags.id', this.tagIds));
            }

            if (conditions.length === 0) {
                return [];
            }

            const criteria = new Criteria(1, LIMIT);

            criteria.addFilter(Criteria.equals('active', true));
            criteria.addFilter(Criteria.multi('OR', conditions));
            criteria.addSorting(Criteria.sort('title', 'ASC'));

            const result = await this.faqRepository.search(criteria, Context.api);

            return [...result];
        },
    },
};
