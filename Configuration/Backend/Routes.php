<?php
return [
    'cleanuptools_ajax' => [
        'path' => '/cleanuptools/ajax',
        'target' => ChristianReifenscheid\CleanupTools\Controller\AjaxController::class . '::mainAction'
    ]
];
