define([
    'jquery'
], function($) {
    'use strict';

    let ToolbarActions = {};

    ToolbarActions.process = function(uri) {
        $.ajax({
            url: uri,
            method: 'post',
            success: function(response) {
            	if (response.status === 'ok') {
            		top.TYPO3.Notification.success(response.headline, response.message);
            	} else {
            		top.TYPO3.Notification.error(response.headline, response.message);
            	}
            },
            error: function() {
        		top.TYPO3.Notification.error('Something went wrong', 'Unfortunately, the ajax call failed.');
            }
        });
    };

    // expose to global
    TYPO3.ToolbarActions = ToolbarActions;

    return ToolbarActions;
});