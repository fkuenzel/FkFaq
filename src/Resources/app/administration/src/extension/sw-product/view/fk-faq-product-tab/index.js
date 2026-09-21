import template from './fk-faq-product-tab.html.twig';
import './fk-faq-product-tab.scss';

const { Mixin, Context } = Shopware;
const { Criteria } = Shopware.Data;

const LIMIT = 100;

/**
 * Reiter "FAQ" auf der Produktdetailseite.
 *
 * Der Reiter schreibt nichts selbst. Er arbeitet auf den Assoziationen, die
 * die ProductExtension an die Produkt-Entity haengt
 * (product.extensions.fkFaqAssignments, .fkFaqCategoryRules, .fkFaqTagRules).
 * Gespeichert wird ueber den normalen Produkt-Speichern-Button, genau wie bei
 * Medien, Preisen oder Cross-Sellings.
 */
export default {
    template,

    inject: [
        'repositoryFactory',
        'acl',
    ],

    mixins: [
        Mixin.getByName('notification'),
    ],

    data() {
        return {
            faqIds: [],
            categoryIds: [],
            tagIds: [],
            manualFaqs: [],
            ruleFaqs: [],
            isLoadingPreview: false,
            isInitialising: true,
        };
    },

    computed: {
        product() {
            return Shopware.Store.get('swProductDetail').product;
        },

        isLoading() {
            return Shopware.Store.get('swProductDetail').isLoading;
        },

        allowEdit() {
            return this.acl.can('product.editor');
        },

        assignmentCollection() {
            return this.product?.extensions?.fkFaqAssignments ?? null;
        },

        categoryRuleCollection() {
            return this.product?.extensions?.fkFaqCategoryRules ?? null;
        },

        tagRuleCollection() {
            return this.product?.extensions?.fkFaqTagRules ?? null;
        },

        /**
         * Fehlen die Assoziationen, wurde das Produkt ohne den Criteria-Override
         * geladen. Dann darf der Reiter nichts anfassen, sonst scheitert das
         * Speichern des gesamten Produkts.
         */
        isReady() {
            return !!this.assignmentCollection
                && !!this.categoryRuleCollection
                && !!this.tagRuleCollection;
        },

        assignmentRepository() {
            return this.repositoryFactory.create('fk_faq_product_assignment');
        },

        categoryRuleRepository() {
            return this.repositoryFactory.create('fk_faq_product_category_rule');
        },

        tagRuleRepository() {
            return this.repositoryFactory.create('fk_faq_product_tag_rule');
        },

        faqRepository() {
            return this.repositoryFactory.create('fk_faq');
        },

        faqCategoryRepository() {
            return this.repositoryFactory.create('fk_faq_category');
        },

        faqTagRepository() {
            return this.repositoryFactory.create('fk_faq_tag');
        },

        faqSelectCriteria() {
            const criteria = new Criteria(1, 25);

            criteria.addSorting(Criteria.sort('title', 'ASC'));

            return criteria;
        },

        nameSelectCriteria() {
            const criteria = new Criteria(1, 25);

            criteria.addSorting(Criteria.sort('name', 'ASC'));

            return criteria;
        },

        hasRules() {
            return this.categoryIds.length > 0 || this.tagIds.length > 0;
        },
    },

    watch: {
        isReady: {
            immediate: true,
            handler(value) {
                if (value) {
                    this.readFromProduct();
                }
            },
        },

        faqIds() {
            this.syncAssignments();
            this.loadPreview();
        },

        categoryIds() {
            this.syncRules(this.categoryRuleCollection, this.categoryRuleRepository, 'fkFaqCategoryId', this.categoryIds);
            this.loadPreview();
        },

        tagIds() {
            this.syncRules(this.tagRuleCollection, this.tagRuleRepository, 'fkFaqTagId', this.tagIds);
            this.loadPreview();
        },
    },

    methods: {
        /**
         * Uebernimmt den geladenen Zustand des Produkts in die Auswahlfelder.
         */
        readFromProduct() {
            this.isInitialising = true;

            this.faqIds = [...this.assignmentCollection]
                .sort((a, b) => (a.position ?? 0) - (b.position ?? 0))
                .map((assignment) => assignment.fkFaqId);

            this.categoryIds = [...this.categoryRuleCollection].map((rule) => rule.fkFaqCategoryId);
            this.tagIds = [...this.tagRuleCollection].map((rule) => rule.fkFaqTagId);

            this.$nextTick(() => {
                this.isInitialising = false;
            });

            this.loadPreview();
        },

        /**
         * Gleicht die manuellen Zuordnungen mit der Auswahl ab.
         * Geschrieben wird nichts - die Collection ist Teil des Produkt-Changesets.
         */
        syncAssignments() {
            if (!this.isReady || this.isInitialising) {
                return;
            }

            const collection = this.assignmentCollection;

            [...collection]
                .filter((assignment) => !this.faqIds.includes(assignment.fkFaqId))
                .forEach((assignment) => collection.remove(assignment.id));

            this.faqIds.forEach((faqId, position) => {
                let assignment = [...collection].find((entry) => entry.fkFaqId === faqId);

                if (!assignment) {
                    assignment = this.assignmentRepository.create(Context.api);
                    assignment.fkFaqId = faqId;

                    collection.add(assignment);
                }

                assignment.position = position;
            });
        },

        /**
         * Gleicht eine Regel-Collection mit der Auswahl ab.
         */
        syncRules(collection, repository, referenceProperty, selectedIds) {
            if (!this.isReady || this.isInitialising) {
                return;
            }

            [...collection]
                .filter((rule) => !selectedIds.includes(rule[referenceProperty]))
                .forEach((rule) => collection.remove(rule.id));

            selectedIds.forEach((referenceId) => {
                const exists = [...collection].some((rule) => rule[referenceProperty] === referenceId);

                if (exists) {
                    return;
                }

                const rule = repository.create(Context.api);
                rule[referenceProperty] = referenceId;

                collection.add(rule);
            });
        },

        /**
         * Vorschau: manuelle Auswahl plus die aus den Regeln resultierenden FAQs.
         */
        async loadPreview() {
            this.isLoadingPreview = true;

            try {
                const [manual, rules] = await Promise.all([
                    this.loadManualFaqs(),
                    this.loadRuleFaqs(),
                ]);

                this.manualFaqs = manual;
                this.ruleFaqs = rules;
            } catch (error) {
                this.createNotificationError({
                    message: `${this.$t('fk-faq-product.tab.notificationPreviewError')} ${this.describeError(error)}`.trim(),
                });

                // eslint-disable-next-line no-console
                console.error('[FkFaq] Vorschau konnte nicht geladen werden', error);
            } finally {
                this.isLoadingPreview = false;
            }
        },

        async loadManualFaqs() {
            if (this.faqIds.length === 0) {
                return [];
            }

            const criteria = new Criteria(1, LIMIT);
            criteria.setIds(this.faqIds);

            const result = await this.faqRepository.search(criteria, Context.api);

            // Reihenfolge der Auswahl ist die Ausgabereihenfolge.
            return this.faqIds
                .map((id) => [...result].find((faq) => faq.id === id))
                .filter(Boolean);
        },

        async loadRuleFaqs() {
            if (!this.hasRules) {
                return [];
            }

            const conditions = [];

            if (this.categoryIds.length > 0) {
                conditions.push(Criteria.equalsAny('categoryId', this.categoryIds));
            }

            if (this.tagIds.length > 0) {
                conditions.push(Criteria.equalsAny('tags.id', this.tagIds));
            }

            const criteria = new Criteria(1, LIMIT);

            criteria.addFilter(Criteria.equals('active', true));
            criteria.addFilter(Criteria.multi('OR', conditions));
            criteria.addSorting(Criteria.sort('title', 'ASC'));

            const result = await this.faqRepository.search(criteria, Context.api);

            return [...result].filter((faq) => !this.faqIds.includes(faq.id));
        },

        describeError(error) {
            const apiError = error?.response?.data?.errors?.[0];

            if (apiError) {
                return [
                    apiError.detail,
                    apiError.source?.pointer,
                ].filter(Boolean).join(' ');
            }

            return error?.message ?? '';
        },
    },
};
