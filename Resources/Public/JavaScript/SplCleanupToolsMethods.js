define([
    'jquery'
], function ($) {
    'use strict';

    let SplCleanupToolsMethods = {};

    SplCleanupToolsMethods.process = function (uri, recordUid = null) {
        console.log(uri);

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
    TYPO3.SplCleanupToolsMethods = SplCleanupToolsMethods;

    return SplCleanupToolsMethods;
});