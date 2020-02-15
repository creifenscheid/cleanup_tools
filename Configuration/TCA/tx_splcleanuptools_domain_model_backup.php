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
        'searchFields' => 'crdate,cruser_id,original_uid, table, data'
    ],
    
    'interface' => [
        'showRecordFieldList' => 'crdate,cruser_id,original_uid, table, data'
    ],
    
    'types' => [
        '0' => [
            'showitem' => '
                --palette--;LLL:EXT:spl_cleanup_tools/Resources/Private/Language/locallang_tca.xlf:tx_splcleanuptools_domain_model_backup.palettes.creation;creation,
                --palette--;LLL:EXT:spl_cleanup_tools/Resources/Private/Language/locallang_tca.xlf:tx_splcleanuptools_domain_model_backup.palettes.backupData;backupData,
            '
        ],
    ],
    
    'palettes' => [
        'creation' => [
            'showitem' => 'crdate,cruser_id',
        ],
        'backupData' => [
            'showitem' => 'original_uid, --linebreak--, table, --linebreak--, data',
        ],
    ],
    
    'columns' => [
        'crdate' => [
            'exclude' => true,
            'label' => 'LLL:EXT:spl_cleanup_tools/Resources/Private/Language/locallang_tca.xlf:tx_splcleanuptools_domain_model_backup.crdate',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'eval' => 'datetime',
                'readOnly' => true
            ]
        ],
        
        'cruser_id' => [
            'exclude' => true,
            'label' => 'LLL:EXT:spl_cleanup_tools/Resources/Private/Language/locallang_tca.xlf:tx_splcleanuptools_domain_model_backup.cruser_id',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'be_users',
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
        ]
    ]
];