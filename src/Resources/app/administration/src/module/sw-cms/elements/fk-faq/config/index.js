import template from './sw-cms-el-config-fk-faq.html.twig';

const { Mixin } = Shopware;
const { Criteria } = Shopware.Data;

export default {
    template,

    inject: ['repositoryFactory'],

    emits: ['element-update'],

    mixins: [
        Mixin.getByName('cms-element'),
    ],

    computed: {
        faqRepository() {
            return this.repositoryFactory.create('fk_faq');
        },

        faqCategoryRepository() {
            return this.repositoryFactory.create('fk_faq_category');
        },

        faqTagRepository() {
            return this.repositoryFactory.create('fk_faq_tag');
        },

        faqCriteria() {
            const criteria = new Criteria(1, 25);

            criteria.addSorting(Criteria.sort('title', 'ASC'));

            return criteria;
        },

        nameCriteria() {
            const criteria = new Criteria(1, 25);

            criteria.addSorting(Criteria.sort('name', 'ASC'));

            return criteria;
        },

        /*
         * Die Konfigurationswerte liegen als { source, value } vor. Die
         * Auswahlfelder arbeiten auf value, das Schreiben geht deshalb ueber
         * berechnete Eigenschaften mit Setter statt ueber v-model auf element.
         */
        faqIds: {
            get() {
                return this.element.config.faqIds.value ?? [];
            },
            set(value) {
                this.updateConfig('faqIds', value);
            },
        },

        categoryIds: {
            get() {
                return this.element.config.categoryIds.value ?? [];
            },
            set(value) {
                this.updateConfig('categoryIds', value);
            },
        },

        tagIds: {
            get() {
                return this.element.config.tagIds.value ?? [];
            },
            set(value) {
                this.updateConfig('tagIds', value);
            },
        },

        headline: {
            get() {
                return this.element.config.headline.value ?? '';
            },
            set(value) {
                this.updateConfig('headline', value);
            },
        },

        showHeadline: {
            get() {
                return this.element.config.showHeadline.value ?? true;
            },
            set(value) {
                this.updateConfig('showHeadline', value);
            },
        },
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.initElementConfig('fk-faq');
        },

        updateConfig(key, value) {
            this.element.config[key].value = value;

            this.$emit('element-update', this.element);
        },
    },
};
