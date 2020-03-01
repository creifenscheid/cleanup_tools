<?php

return [
    'ctrl' => [
        #'hideTable' => true,
        'title' => 'LLL:EXT:spl_cleanup_tools/Resources/Private/Language/locallang_tca.xlf:tx_splcleanuptools_domain_model_backup',
        'label' => 'original_uid',
        'label_alt' => 'table',
        'label_alt_force' => 1,
        'iconfile' => 'EXT:spl_cleanup_tools/Resources/Public/Icons/tx_splcleanuptools_domain_model_backup.svg',
        'sortby' => 'crdate',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'delete' => 'deleted',
        'versioningWS' => false,
        'searchFields' => 'log, original_uid, table, data'
    ],
    
    'interface' => [
        'showRecordFieldList' => 'log, original_uid, table, data'
    ],
    
    'types' => [
        '0' => [
            'showitem' => '
                --palette--;LLL:EXT:spl_cleanup_tools/Resources/Private/Language/locallang_tca.xlf:tx_splcleanuptools_domain_model_backup.palettes.backupData;backupData,
            '
        ],
    ],
    
    'palettes' => [
        'backupData' => [
            'showitem' => 'log, restored, --linebreak--, original_uid, --linebreak--, table, --linebreak--, data',
        ],
    ],
    
    'columns' => [
        'log' => [
            'exclude' => true,
            'label' => 'LLL:EXT:spl_cleanup_tools/Resources/Private/Language/locallang_tca.xlf:tx_splcleanuptools_domain_model_backup.log',
            'config' => [
                'type' => 'select',
                'foreign_table' => 'tx_splcleanuptools_domain_model_log',
                'readOnly' => true
            ]
        ],
        
        'original_uid' => [
            'exclude' => true,
            'label' => 'LLL:EXT:spl_cleanup_tools/Resources/Private/Language/locallang_tca.xlf:tx_splcleanuptools_domain_model_backup.original_uid',
            'config' => [
                'type' => 'input',
                'readOnly' => true
            ]
        ],
        
        'table' => [
            'exclude' => true,
            'label' => 'LLL:EXT:spl_cleanup_tools/Resources/Private/Language/locallang_tca.xlf:tx_splcleanuptools_domain_model_backup.table',
            'config' => [
                'type' => 'input',
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
        ],
        
        'restored' => [
            'exclude' => true,
            'label' => 'LLL:EXT:spl_cleanup_tools/Resources/Private/Language/locallang_tca.xlf:tx_splcleanuptools_domain_model_backup.restored',
            'config' => [
                'default' => 0,
                'type' => 'check',
                'readOnly' => true
            ]
        ]
    ]
];