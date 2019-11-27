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
                console.log(response); // ToDo: alert fly
            },
            error: function(response) {
                console.log(response); // ToDo: alert fly
            }
        });
    };

    // expose to global
    TYPO3.ToolbarActions = ToolbarActions;

    return ToolbarActions;
});