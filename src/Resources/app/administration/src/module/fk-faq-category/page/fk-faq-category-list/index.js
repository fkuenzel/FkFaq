import template from './fk-faq-category-list.html.twig';

const { Mixin } = Shopware;
const { Criteria } = Shopware.Data;

export default {
    template,

    inject: ['repositoryFactory'],

    mixins: [
        Mixin.getByName('listing'),
        Mixin.getByName('notification'),
    ],

    data() {
        return {
            categories: null,
            isLoading: true,
            sortBy: 'name',
            sortDirection: 'ASC',
            total: 0,
        };
    },

    metaInfo() {
        return {
            title: this.$createTitle(),
        };
    },

    computed: {
        categoryRepository() {
            return this.repositoryFactory.create('fk_faq_category');
        },

        categoryCriteria() {
            const criteria = new Criteria(this.page, this.limit);

            criteria.setTerm(this.term);
            criteria.addSorting(Criteria.sort(this.sortBy, this.sortDirection, this.naturalSorting));

            return criteria;
        },

        categoryColumns() {
            return [
                {
                    property: 'name',
                    dataIndex: 'name',
                    label: 'fk-faq-category.list.columnName',
                    routerLink: 'fk.faq.category.detail',
                    inlineEdit: 'string',
                    allowResize: true,
                    primary: true,
                },
                {
                    property: 'createdAt',
                    label: 'fk-faq-category.list.columnCreatedAt',
                    allowResize: true,
                },
            ];
        },
    },

    methods: {
        onChangeLanguage(languageId) {
            this.getList(languageId);
        },

        async getList() {
            this.isLoading = true;

            try {
                const result = await this.categoryRepository.search(this.categoryCriteria);

                this.categories = result;
                this.total = result.total;
            } catch (error) {
                this.createNotificationError({
                    message: this.$t('global.notification.notificationLoadingDataErrorMessage'),
                });

                throw error;
            } finally {
                this.isLoading = false;
            }
        },

        updateTotal({ total }) {
            this.total = total;
        },
    },
};
