<?php
return [
    'cleanuptools_ajax' => [
        'path' => '/cleanuptools/ajax',
        'target' => creifenscheid\CleanupTools\Controller\AjaxController::class . '::mainAction'
    ]
];
