<?php

return [
    'ctrl' => [
        'hideTable' => true,
        'adminOnly' => true,
        'title' => 'LLL:EXT:cleanup_tools/Resources/Private/Language/locallang_tca.xlf:tx_cleanuptools_domain_model_log',
        'label' => 'service',
        'label_alt' => 'crdate, execution_context',
        'label_alt_force' => true,
        'iconfile' => 'EXT:cleanup_tools/Resources/Public/Icons/tx_cleanuptools_domain_model_log.svg',
        'sortby' => 'crdate',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'delete' => 'deleted',
        'versioningWS' => false,
        'searchFields' => 'crdate,cruser_id,execution_context,service,parameters,state,messages'
    ],

    'interface' => [
        'showRecordFieldList' => 'crdate,cruser_id,execution_context,service,state,parameters,messages'
    ],

    'types' => [
        '0' => [
            'showitem' => '
                --palette--;LLL:EXT:cleanup_tools/Resources/Private/Language/locallang_tca.xlf:tx_cleanuptools_domain_model.general.palettes.creation;creation,
                --palette--;LLL:EXT:cleanup_tools/Resources/Private/Language/locallang_tca.xlf:tx_cleanuptools_domain_model_log.palettes.logData;logData,
                --div--;LLL:EXT:cleanup_tools/Resources/Private/Language/locallang_tca.xlf:tx_cleanuptools_domain_model_log.tab.messages,messages,
            '
        ]
    ],

    'palettes' => [
        'creation' => [
            'showitem' => 'crdate,cruser_id',
        ],
        'logData' => [
            'showitem' => 'state,execution_context,--linebreak--,service,--linebreak--,parameters',
        ]
    ],

    'columns' => [
        'crdate' => [
            'exclude' => true,
            'label' => 'LLL:EXT:cleanup_tools/Resources/Private/Language/locallang_tca.xlf:tx_cleanuptools_domain_model.general.crdate',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'eval' => 'datetime',
                'readOnly' => true
            ]
        ],

        'cruser_id' => [
            'exclude' => true,
            'label' => 'LLL:EXT:cleanup_tools/Resources/Private/Language/locallang_tca.xlf:tx_cleanuptools_domain_model.general.cruser_id',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'be_users',
                'readOnly' => true
            ]
        ],

        'execution_context' => [
            'exclude' => true,
            'label' => 'LLL:EXT:cleanup_tools/Resources/Private/Language/locallang_tca.xlf:tx_cleanuptools_domain_model_log.execution_context',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['LLL:EXT:cleanup_tools/Resources/Private/Language/locallang_mod.xlf:tx_cleanuptools.general.executioncontext.0', 0],
                    ['LLL:EXT:cleanup_tools/Resources/Private/Language/locallang_mod.xlf:tx_cleanuptools.general.executioncontext.1', 1],
                    ['LLL:EXT:cleanup_tools/Resources/Private/Language/locallang_mod.xlf:tx_cleanuptools.general.executioncontext.2', 2],
                    ['LLL:EXT:cleanup_tools/Resources/Private/Language/locallang_mod.xlf:tx_cleanuptools.general.executioncontext.3', 3],
                    ['LLL:EXT:cleanup_tools/Resources/Private/Language/locallang_mod.xlf:tx_cleanuptools.general.executioncontext.4', 4]
                ],
                'readOnly' => true
            ]
        ],

        'service' => [
            'exclude' => true,
            'label' => 'LLL:EXT:cleanup_tools/Resources/Private/Language/locallang_tca.xlf:tx_cleanuptools_domain_model_log.service',
            'config' => [
                'type' => 'input',
                'readOnly' => true
            ]
        ],
        
        'parameters' => [
            'exclude' => true,
            'label' => 'LLL:EXT:cleanup_tools/Resources/Private/Language/locallang_tca.xlf:tx_cleanuptools_domain_model_log.parameters',
            'config' => [
                'type' => 'text',
                'readOnly' => true
            ]
        ],

        'state' => [
            'exclude' => true,
            'label' => 'LLL:EXT:cleanup_tools/Resources/Private/Language/locallang_tca.xlf:tx_cleanuptools_domain_model_log.state',
            'config' => [
                'default' => 1,
                'type' => 'check',
                'readOnly' => true
            ]
        ],
        
        'messages' => [
            'label' => 'LLL:EXT:cleanup_tools/Resources/Private/Language/locallang_tca.xlf:tx_cleanuptools_domain_model_log.messages',
            'config' => [
                'type' => 'inline',
                'foreign_table' => 'tx_cleanuptools_domain_model_log_message',
                'foreign_field' => 'log'
            ]
        ]
    ]
];