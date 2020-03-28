define([
    'jquery'
], function ($) {
    'use strict';

    let SplCleanupTools = {};

    SplCleanupTools.process = function (uri, recordUid = null) {
        $.ajax({
            url: uri,
            method: 'post',
            success: function (response) {
                if (response.status === 'ok') {
                    top.TYPO3.Notification.success(response.headline, response.message);

                    if (recordUid) {
                        const hideElementOnSuccess = $('.hide-on-success-' + recordUid);

                        if (hideElementOnSuccess.length) {
                            hideElementOnSuccess.hide();
                        }

                    }
                } else if (response.status === 'info'){
                	top.TYPO3.Notification.info(response.headline, response.message, 0);
                } else {
                    top.TYPO3.Notification.error(response.headline, response.message);
                }
            },
            error: function () {
                top.TYPO3.Notification.error('Something went wrong', 'Unfortunately, the ajax call failed.');
            }
        });
    };

    // expose to global
    TYPO3.SplCleanupTools = SplCleanupTools;

    return SplCleanupTools;
});