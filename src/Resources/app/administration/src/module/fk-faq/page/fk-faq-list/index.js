import template from './fk-faq-list.html.twig';

const { Mixin } = Shopware;
const { Criteria } = Shopware.Data;

export default {
    template,

    inject: [
        'repositoryFactory',
        'filterFactory',
    ],

    mixins: [
        Mixin.getByName('listing'),
        Mixin.getByName('notification'),
    ],

    data() {
        return {
            faqs: null,
            isLoading: true,
            /*
             * Ohne disableRouteParams laeuft die Filterkette in ein Timing-Problem:
             * der filterService schiebt den Filterzustand per router.push in die URL,
             * bevor sw-filter-panel criteria-changed ausloest. Der $route-Watcher des
             * listing-Mixins laedt dann noch mit leeren Filtern und verwirft beim
             * naechsten Routenwechsel sogar die gerade gesetzten Filter.
             * Mit disableRouteParams laeuft genau ein getList() pro Filteraenderung.
             * Preis: Seite, Sortierung und Suchbegriff stehen nicht in der URL.
             * Die Filter selbst bleiben erhalten, der filterService speichert sie
             * benutzerbezogen in user_config.
             */
            disableRouteParams: true,
            sortBy: 'position',
            sortDirection: 'ASC',
            total: 0,
            filterCriteria: [],
            defaultFilters: [
                'category-filter',
                'tags-filter',
                'active-filter',
            ],
            storeKey: 'grid.filter.fk_faq',
            activeFilterNumber: 0,
        };
    },

    metaInfo() {
        return {
            title: this.$createTitle(),
        };
    },

    computed: {
        faqRepository() {
            return this.repositoryFactory.create('fk_faq');
        },

        faqCriteria() {
            const criteria = new Criteria(this.page, this.limit);

            criteria.setTerm(this.term);
            criteria.addAssociation('category');
            criteria.addAssociation('tags');
            criteria.addSorting(Criteria.sort(this.sortBy, this.sortDirection, this.naturalSorting));

            this.filterCriteria.forEach((filter) => {
                criteria.addFilter(filter);
            });

            return criteria;
        },

        listFilterOptions() {
            return {
                'category-filter': {
                    property: 'category',
                    label: this.$t('fk-faq.filters.categoryFilter.label'),
                    placeholder: this.$t('fk-faq.filters.categoryFilter.placeholder'),
                },
                'tags-filter': {
                    property: 'tags',
                    label: this.$t('fk-faq.filters.tagsFilter.label'),
                    placeholder: this.$t('fk-faq.filters.tagsFilter.placeholder'),
                },
                'active-filter': {
                    property: 'active',
                    label: this.$t('fk-faq.filters.activeFilter.label'),
                    placeholder: this.$t('fk-faq.filters.activeFilter.placeholder'),
                },
            };
        },

        listFilters() {
            return this.filterFactory.create('fk_faq', this.listFilterOptions);
        },

        faqColumns() {
            return [
                {
                    property: 'title',
                    dataIndex: 'title',
                    label: 'fk-faq.list.columnTitle',
                    routerLink: 'fk.faq.detail',
                    inlineEdit: 'string',
                    allowResize: true,
                    primary: true,
                },
                {
                    property: 'category.name',
                    label: 'fk-faq.list.columnCategory',
                    allowResize: true,
                    sortable: false,
                },
                {
                    property: 'tags',
                    label: 'fk-faq.list.columnTags',
                    allowResize: true,
                    sortable: false,
                },
                {
                    property: 'active',
                    label: 'fk-faq.list.columnActive',
                    inlineEdit: 'boolean',
                    align: 'center',
                    allowResize: true,
                },
                {
                    property: 'position',
                    label: 'fk-faq.list.columnPosition',
                    inlineEdit: 'number',
                    align: 'right',
                    allowResize: true,
                },
                {
                    property: 'createdAt',
                    label: 'fk-faq.list.columnCreatedAt',
                    allowResize: true,
                    visible: false,
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
            this.activeFilterNumber = this.filterCriteria.length;

            try {
                const result = await this.faqRepository.search(this.faqCriteria);

                this.faqs = result;
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

        onRefresh() {
            this.getList();
        },

        updateTotal({ total }) {
            this.total = total;
        },
    },
};
