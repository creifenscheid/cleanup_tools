define([
    'jquery'
], function ($) {
    'use strict';

    let CleanupTools = {};

    CleanupTools.process = function (uri, recordUid = null) {
        $.ajax({
            url: uri,
            method: 'post',
            success: function (response) {
            	switch (response.severity) {
            		case '-2':
          	  			top.TYPO3.Notification.notice(response.headline, response.message);
          	  			break;
            		case '-1':
            			top.TYPO3.Notification.info(response.headline, response.message);
            			break;
            		case '1':
            			top.TYPO3.Notification.warning(response.headline, response.message);
            			break;
            		case '2':
            			top.TYPO3.Notification.error(response.headline, response.message);
            			break;
            		case '0':
            			top.TYPO3.Notification.success(response.headline, response.message);
            			
            			if (recordUid) {
            				const hideElementOnSuccess = $('.hide-on-success-' + recordUid);
            				
            				if (hideElementOnSuccess.length) {
            					hideElementOnSuccess.hide();
            				}
            			}
            			break;
            	}
            },
            error: function () {
                top.TYPO3.Notification.error('Something went wrong', 'Unfortunately, the ajax call failed.');
            }
        });
    };
    
    CleanupTools.toggleMessage = function (element) {
    	const elementObject = $(element);
    	const toggleGroup = elementObject.attr('data-group');

    	if (elementObject.hasClass('expanded')) {
    		elementObject.removeClass('expanded');
    		elementObject.attr('aria-expanded', 'false');
    		$('.log-message-group-' + toggleGroup).hide();
    	} else {
    		elementObject.addClass('expanded');
    		elementObject.attr('aria-expanded', 'true');
    		$('.log-message-group-' + toggleGroup).show();
    	}
    }

    // expose to global
    TYPO3.CleanupTools = CleanupTools;

    return CleanupTools;
});