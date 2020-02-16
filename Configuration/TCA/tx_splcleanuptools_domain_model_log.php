<?php

return [
    'ctrl' => [
        'title' => 'LLL:EXT:spl_cleanup_tools/Resources/Private/Language/locallang_tca.xlf:tx_splcleanuptools_domain_model_log',
        'label' => 'utility',
        'label_alt' => 'action, crdate',
        'label_alt_force' => 1,
        'iconfile' => 'EXT:spl_cleanup_tools/Resources/Public/Icons/tx_splcleanuptools_domain_model_log.svg',
        'sortby' => 'crdate',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'delete' => 'deleted',
        'versioningWS' => false,
        'searchFields' => 'crdate,cruser_id,utility,action,backups'
    ],
    
    'interface' => [
        'showRecordFieldList' => 'crdate,cruser_id,utility,action,backups'
    ],
    
    'types' => [
        '0' => [
            'showitem' => '
                --palette--;LLL:EXT:spl_cleanup_tools/Resources/Private/Language/locallang_tca.xlf:tx_splcleanuptools_domain_model.general.palettes.creation;creation,
                --palette--;LLL:EXT:spl_cleanup_tools/Resources/Private/Language/locallang_tca.xlf:tx_splcleanuptools_domain_model_log.palettes.logData;logData,
            '
        ],
    ],
    
    'palettes' => [
        'creation' => [
            'showitem' => 'crdate,cruser_id',
        ],
        'logData' => [
            'showitem' => 'utility,action,--linebreak--,backups',
        ],
    ],
    
    'columns' => [
        'crdate' => [
            'exclude' => true,
            'label' => 'LLL:EXT:spl_cleanup_tools/Resources/Private/Language/locallang_tca.xlf:tx_splcleanuptools_domain_model.general.crdate',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'eval' => 'datetime',
                'readOnly' => true
            ]
        ],
        
        'cruser_id' => [
            'exclude' => true,
            'label' => 'LLL:EXT:spl_cleanup_tools/Resources/Private/Language/locallang_tca.xlf:tx_splcleanuptools_domain_model.general.cruser_id',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'be_users',
                'readOnly' => true
            ]
        ],
        
        'utility' => [
            'exclude' => true,
            'label' => 'LLL:EXT:spl_cleanup_tools/Resources/Private/Language/locallang_tca.xlf:tx_splcleanuptools_domain_model_log.utility',
            'config' => [
                'type' => 'input',
                'readOnly' => true
            ]
        ],
        
        'action' => [
            'exclude' => true,
            'label' => 'LLL:EXT:spl_cleanup_tools/Resources/Private/Language/locallang_tca.xlf:tx_splcleanuptools_domain_model_log.action',
            'config' => [
                'type' => 'input',
                'readOnly' => true
            ]
        ],
        
        'backups' => [
            'label' => 'LLL:EXT:spl_cleanup_tools/Resources/Private/Language/locallang_tca.xlf:tx_splcleanuptools_domain_model_log.backups',
            'config' => [
                'type' => 'inline',
                'foreign_table' => 'tx_splcleanuptools_domain_model_backup',
                'foreign_field' => 'log'
            ],
            
        ],
    ]
];