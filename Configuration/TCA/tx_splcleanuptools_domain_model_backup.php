<?php

return [
    'ctrl' => [
        'title' => 'LLL:EXT:spl_cleanup_tools/Resources/Private/Language/locallang_tca.xlf:tx_splcleanuptools_domain_model_backup',
        'label' => 'original_uid',
        'label_alt' => 'table, crdate',
        'label_alt_force' => 1,
        'iconfile' => 'EXT:spl_cleanup_tools/Resources/Public/Icons/tx_splcleanuptools_domain_model_backup.svg',
        'sortby' => 'crdate',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'delete' => 'deleted',
        'versioningWS' => false,
        'searchFields' => 'original_uid, table, data'
    ],
    
    'interface' => [
        'showRecordFieldList' => 'original_uid, table, data'
    ],
    
    'types' => [
        '0' => ['showitem' => 'original_uid, table, data'],
    ],
    
    'columns' => [
        'original_uid' => [
            'exclude' => true,
            'label' => 'LLL:EXT:spl_cleanup_tools/Resources/Private/Language/locallang_tca.xlf:tx_splcleanuptools_domain_model_backup.original_uid',
            'config' => [
                'type' => 'input',
                'size' => '256',
                'readOnly' => true
            ]
        ],
        
        'table' => [
            'exclude' => true,
            'label' => 'LLL:EXT:spl_cleanup_tools/Resources/Private/Language/locallang_tca.xlf:tx_splcleanuptools_domain_model_backup.table',
            'config' => [
                'type' => 'input',
                'size' => '256',
                'readOnly' => true
            ]
        ],
        
        'data' => [
            'exclude' => true,
            'label' => 'LLL:EXT:spl_cleanup_tools/Resources/Private/Language/locallang_tca.xlf:tx_splcleanuptools_domain_model_backup.data',
            'config' => [
                'type' => 'text',
                'cols' => 50,
                'readOnly' => true
            ]
        ]
    ]
];