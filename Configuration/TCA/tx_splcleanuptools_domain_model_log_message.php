<?php

return [
    'ctrl' => [
        'hideTable' => true,
        'title' => 'LLL:EXT:spl_cleanup_tools/Resources/Private/Language/locallang_tca.xlf:tx_splcleanuptools_domain_model_log_message',
        'label' => 'message',
        'label_alt' => 'log',
        'label_alt_force' => 1,
        'iconfile' => 'EXT:spl_cleanup_tools/Resources/Public/Icons/tx_splcleanuptools_domain_model_log_message.svg',
        'sortby' => 'crdate',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'delete' => 'deleted',
        'versioningWS' => false,
        'searchFields' => 'log,message,local_lang_key,local_lang_arguments'
    ],

    'interface' => [
        'showRecordFieldList' => 'log,message,local_lang_key,local_lang_arguments'
    ],

    'types' => [
        '0' => [
            'showitem' => 'log,message,local_lang_key,local_lang_arguments'
        ],
    ],

    'columns' => [
        
        'log' => [
            'exclude' => true,
            'label' => 'LLL:EXT:spl_cleanup_tools/Resources/Private/Language/locallang_tca.xlf:tx_splcleanuptools_domain_model_log_message.log',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'tx_splcleanuptools_domain_model_log',
                'readOnly' => true
            ]
        ],

        'message' => [
            'exclude' => true,
            'label' => 'LLL:EXT:spl_cleanup_tools/Resources/Private/Language/locallang_tca.xlf:tx_splcleanuptools_domain_model_log_message.message',
            'config' => [
                'default' => 1,
                'type' => 'text',
                'readOnly' => true
            ]
        ],

        'local_lang_key' => [
            'exclude' => true,
            'label' => 'LLL:EXT:spl_cleanup_tools/Resources/Private/Language/locallang_tca.xlf:tx_splcleanuptools_domain_model_log_message.local_lang_key',
            'config' => [
                'default' => 1,
                'type' => 'input',
                'readOnly' => true
            ]
        ],

        'local_lang_arguments' => [
            'exclude' => true,
            'label' => 'LLL:EXT:spl_cleanup_tools/Resources/Private/Language/locallang_tca.xlf:tx_splcleanuptools_domain_model_log_message.local_lang_arguments',
            'config' => [
                'default' => 1,
                'type' => 'text',
                'readOnly' => true
            ]
        ]
    ]
];