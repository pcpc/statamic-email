<template>
    <div class="locale-settings-fieldtype-wrapper">
        <grid-fieldtype :data="data" :config="gridConfig"></grid-fieldtype>
        <div class="alert alert-danger mt-16" v-if="shouldShowIndexPhpWarning">
            <small v-html="translate('cp.settings_locales_index_php_warning', { locale: firstLocale })"></small>
        </div>
    </div>
</template>

<script>
module.exports = {

    props: ['data', 'config', 'name', 'indexPhpWarning'],

    computed: {

        shouldShowIndexPhpWarning() {
            return this.indexPhpWarning && this.firstLocale !== 'en';
        },

        firstLocale() {
            return this.data.length ? this.data[0].locale : 'en';
        }

    },

    data: function() {
        return {
            gridConfig: {
                add_row: 'Add Locale',
                fields: [
                    { name: 'locale', type: 'text', display: 'Shorthand', instructions: '2 character code.<br> `en`, `de`, etc.', width: '20%' },
                    { name: 'full', type: 'text', display: 'Full Locale', instructions: 'Used for PHP date localization.<br>`en_US`, `de_DE`, etc.', width: '20%' },
                    { name: 'name', type: 'text', display: 'Name', instructions: 'Used for display.<br> `English`, `German`, etc', width: '20%' },
                    { name: 'url', type: 'text', display: 'URL', instructions: '`http://example.com/de/`, etc.' }
                ]
            }
        }
    }

};
</script>
