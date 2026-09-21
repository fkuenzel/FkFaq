import template from './fk-faq-detail.html.twig';

const { Mixin, Context } = Shopware;
const { Criteria } = Shopware.Data;

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
        faqId: {
            type: String,
            required: false,
            default: null,
        },
    },

    data() {
        return {
            faq: null,
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
            return this.placeholder(this.faq, 'title');
        },

        faqRepository() {
            return this.repositoryFactory.create('fk_faq');
        },

        faqCriteria() {
            const criteria = new Criteria(1, 1);

            criteria.addAssociation('category');
            criteria.addAssociation('tags');

            return criteria;
        },

        tooltipSave() {
            const systemKey = this.$device.getSystemKey();

            return {
                message: `${systemKey} + S`,
                appearance: 'light',
            };
        },
    },

    watch: {
        faqId() {
            this.createdComponent();
        },
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            if (this.faqId) {
                this.loadEntityData();

                return;
            }

            Shopware.Store.get('context').resetLanguageToDefault();

            this.faq = this.faqRepository.create();
            this.faq.active = true;
            this.faq.position = 0;
        },

        async loadEntityData() {
            this.isLoading = true;

            try {
                this.faq = await this.faqRepository.get(this.faqId, Context.api, this.faqCriteria);
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
            return this.faqRepository.hasChanges(this.faq);
        },

        saveOnLanguageChange() {
            return this.onSave();
        },

        onChangeLanguage() {
            if (this.faqId) {
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
                await this.faqRepository.save(this.faq);

                this.isSaveSuccessful = true;

                this.createNotificationSuccess({
                    message: this.$t('fk-faq.detail.notificationSaveSuccess'),
                });

                if (!this.faqId) {
                    this.$router.push({ name: 'fk.faq.detail', params: { id: this.faq.id } });

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
            this.$router.push({ name: 'fk.faq.index' });
        },
    },
};
