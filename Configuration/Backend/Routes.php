<?php
return [
    'cleanuptools_ajax' => [
        'path' => '/cleanuptools/ajax',
        'target' => CReifenscheid\CleanupTools\Controller\AjaxController::class . '::mainAction'
    ]
];
