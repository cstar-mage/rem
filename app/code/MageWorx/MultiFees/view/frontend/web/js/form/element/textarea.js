define([
    'Magento_Ui/js/form/element/textarea',
    'uiRegistry'
], function (Abstract, registry) {
    'use strict';

    return Abstract.extend({
        /**
         * Callback that fires when 'value' property is updated.
         */
        onUpdate: function () {
            this._super();
            var form;
            try {
                form = this.containers[0].containers[0] ?
                    this.containers[0].containers[0] :
                    registry.get('index = mageworx-fee-form-container');
            } catch (e) {
                form = registry.get('index = mageworx-fee-form-container');
            }
            if (this.applyOnClick) {
                form.onSubmit();
            }
        }
    });
});

