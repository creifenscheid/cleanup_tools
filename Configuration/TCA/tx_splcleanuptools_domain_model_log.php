<?php

return [
    'ctrl' => [
        'hideTable' => true,
        'title' => 'LLL:EXT:spl_cleanup_tools/Resources/Private/Language/locallang_tca.xlf:tx_splcleanuptools_domain_model_log',
        'label' => 'service',
        'label_alt' => 'crdate, execution_context',
        'label_alt_force' => 1,
        'iconfile' => 'EXT:spl_cleanup_tools/Resources/Public/Icons/tx_splcleanuptools_domain_model_log.svg',
        'sortby' => 'crdate',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'delete' => 'deleted',
        'versioningWS' => false,
        'searchFields' => 'crdate,cruser_id,execution_context,service,state'
    ],

    'interface' => [
        'showRecordFieldList' => 'crdate,cruser_id,execution_context,service,state'
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
            'showitem' => 'state,execution_context,--linebreak--,service',
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

        'execution_context' => [
            'exclude' => true,
            'label' => 'LLL:EXT:spl_cleanup_tools/Resources/Private/Language/locallang_tca.xlf:tx_splcleanuptools_domain_model_log.execution_context',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['LLL:EXT:spl_cleanup_tools/Resources/Private/Language/locallang_mod.xlf:tx_splcleanuptools.general.executioncontext.0', 0],
                    ['LLL:EXT:spl_cleanup_tools/Resources/Private/Language/locallang_mod.xlf:tx_splcleanuptools.general.executioncontext.1', 1],
                    ['LLL:EXT:spl_cleanup_tools/Resources/Private/Language/locallang_mod.xlf:tx_splcleanuptools.general.executioncontext.2', 2],
                    ['LLL:EXT:spl_cleanup_tools/Resources/Private/Language/locallang_mod.xlf:tx_splcleanuptools.general.executioncontext.3', 3],
                    ['LLL:EXT:spl_cleanup_tools/Resources/Private/Language/locallang_mod.xlf:tx_splcleanuptools.general.executioncontext.4', 4]
                ],
                'readOnly' => true
            ]
        ],

        'service' => [
            'exclude' => true,
            'label' => 'LLL:EXT:spl_cleanup_tools/Resources/Private/Language/locallang_tca.xlf:tx_splcleanuptools_domain_model_log.service',
            'config' => [
                'type' => 'input',
                'readOnly' => true
            ]
        ],

        'state' => [
            'exclude' => true,
            'label' => 'LLL:EXT:spl_cleanup_tools/Resources/Private/Language/locallang_tca.xlf:tx_splcleanuptools_domain_model_log.state',
            'config' => [
                'default' => 1,
                'type' => 'check',
                'readOnly' => true
            ]
        ]
    ]
];