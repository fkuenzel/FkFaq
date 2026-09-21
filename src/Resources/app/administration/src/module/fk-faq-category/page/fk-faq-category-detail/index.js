import template from './fk-faq-category-detail.html.twig';

const { Mixin, Context } = Shopware;

export default {
    template,

    inject: ['repositoryFactory'],

    mixins: [
        Mixin.getByName('notification'),
        Mixin.getByName('placeholder'),
    ],

    shortcuts: {
        'SYSTEMKEY+S': 'onSave',
        ESCAPE: 'onCancel',
    },

    props: {
        categoryId: {
            type: String,
            required: false,
            default: null,
        },
    },

    data() {
        return {
            category: null,
            isLoading: false,
            isSaveSuccessful: false,
        };
    },

    metaInfo() {
        return {
            title: this.$createTitle(this.identifier),
        };
    },

    computed: {
        identifier() {
            return this.placeholder(this.category, 'name');
        },

        categoryRepository() {
            return this.repositoryFactory.create('fk_faq_category');
        },
    },

    watch: {
        categoryId() {
            this.createdComponent();
        },
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            if (this.categoryId) {
                this.loadEntityData();

                return;
            }

            Shopware.Store.get('context').resetLanguageToDefault();

            this.category = this.categoryRepository.create();
        },

        async loadEntityData() {
            this.isLoading = true;

            try {
                this.category = await this.categoryRepository.get(this.categoryId, Context.api);
            } catch (error) {
                this.createNotificationError({
                    message: this.$t('global.notification.notificationLoadingDataErrorMessage'),
                });

                throw error;
            } finally {
                this.isLoading = false;
            }
        },

        abortOnLanguageChange() {
            return this.categoryRepository.hasChanges(this.category);
        },

        saveOnLanguageChange() {
            return this.onSave();
        },

        onChangeLanguage() {
            if (this.categoryId) {
                this.loadEntityData();
            }
        },

        saveFinish() {
            this.isSaveSuccessful = false;
        },

        async onSave() {
            this.isSaveSuccessful = false;
            this.isLoading = true;

            try {
                await this.categoryRepository.save(this.category);

                this.isSaveSuccessful = true;

                this.createNotificationSuccess({
                    message: this.$t('fk-faq-category.detail.notificationSaveSuccess'),
                });

                if (!this.categoryId) {
                    this.$router.push({ name: 'fk.faq.category.detail', params: { id: this.category.id } });

                    return;
                }

                await this.loadEntityData();
            } catch (error) {
                this.createNotificationError({
                    message: this.$t('global.notification.notificationSaveErrorMessageRequiredFieldsInvalid'),
                });

                throw error;
            } finally {
                this.isLoading = false;
            }
        },

        onCancel() {
            this.$router.push({ name: 'fk.faq.category.index' });
        },
    },
};
