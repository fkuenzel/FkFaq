import template from './fk-faq-tag-detail.html.twig';

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
        tagId: {
            type: String,
            required: false,
            default: null,
        },
    },

    data() {
        return {
            tag: null,
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
            return this.placeholder(this.tag, 'name');
        },

        tagRepository() {
            return this.repositoryFactory.create('fk_faq_tag');
        },
    },

    watch: {
        tagId() {
            this.createdComponent();
        },
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            if (this.tagId) {
                this.loadEntityData();

                return;
            }

            Shopware.Store.get('context').resetLanguageToDefault();

            this.tag = this.tagRepository.create();
        },

        async loadEntityData() {
            this.isLoading = true;

            try {
                this.tag = await this.tagRepository.get(this.tagId, Context.api);
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
            return this.tagRepository.hasChanges(this.tag);
        },

        saveOnLanguageChange() {
            return this.onSave();
        },

        onChangeLanguage() {
            if (this.tagId) {
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
                await this.tagRepository.save(this.tag);

                this.isSaveSuccessful = true;

                this.createNotificationSuccess({
                    message: this.$t('fk-faq-tag.detail.notificationSaveSuccess'),
                });

                if (!this.tagId) {
                    this.$router.push({ name: 'fk.faq.tag.detail', params: { id: this.tag.id } });

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
            this.$router.push({ name: 'fk.faq.tag.index' });
        },
    },
};
