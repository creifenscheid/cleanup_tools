<?php
return [
    'splcleanuptools_ajax' => [
        'path' => '/splcleanuptools/ajax',
        'target' => SPL\SplCleanupTools\Controller\AjaxController::class . '::mainAction'
    ]
];
