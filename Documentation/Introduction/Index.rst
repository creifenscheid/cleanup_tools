.. include:: ../Includes.txt


.. _introduction:

============
Introduction
============

Target group: **Administrators**

What does it do?
================

This extension provides the possibility to implement cleanup services in your TYPO3 installation.

It provides the following features:

* backend module to run services
* history module to take a look on previous runs
* info module with extended information about every service
* toolbar to run services in dry-run mode
* scheduler task
* preview renderer to run cleanFlexFormsService
* afterDatabaseOperations hook to run cleanFlexFormsService
* dashboard widget with dry run results of all configured services

Cleanup services are registered in ext_localconf.php via \CReifenscheid\CleanupTools\Utility\ConfigurationManagementUtility.
Therefor it is possible to extend the extension with your own services easily.

Currently available services
============================

CleanFlexFormsService
~~~~~~~~~~~~~~~~~~~~~

.. container:: table-row

    Service
         CleanFlexFormsService

    Description
         Checks if TCA records with a FlexForm includes values that don't match the connected FlexForm value
    
    Source
         \\TYPO3\\CMS\\Lowlevel\\Command\\CleanFlexFormsCommand::class
         
DeletedRecordsService
~~~~~~~~~~~~~~~~~~~~~

.. container:: table-row

    Service
         DeletedRecordsService

    Description
          Force-deletes all records in the database which have a deleted=1 flag
    
    Source
         \\TYPO3\\CMS\\Lowlevel\\Command\DeletedRecordsCommand::class
         
FilesWithMultipleReferencesService
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. container:: table-row

    Service
         FilesWithMultipleReferencesService

    Description
          Finds files within uploads/ which are used multiple times by relations within the database
    
    Source
         \\TYPO3\\CMS\\Lowlevel\\Command\FilesWithMultipleReferencesCommand::class
         
LostFilesService
~~~~~~~~~~~~~~~~~~~~~

.. container:: table-row

    Service
         LostFilesService

    Description
          Finds files within uploads/ which are not needed anymore
    
    Source
         \\TYPO3\\CMS\\Lowlevel\\Command\LostFilesCommand::class
         
MissingFilesService
~~~~~~~~~~~~~~~~~~~

.. container:: table-row

    Service
         MissingFilesService

    Description
          Find all file references from records pointing to a missing (non-existing) file
    
    Source
         \\TYPO3\\CMS\\Lowlevel\\Command\MissingFilesCommand::class
         
MissingRelationsService
~~~~~~~~~~~~~~~~~~~

.. container:: table-row

    Service
         MissingRelationsService

    Description
          Find all record references pointing to a non-existing record
    
    Source
         \\TYPO3\\CMS\\Lowlevel\\Command\MissingRelationsCommand::class
         
OrphanRecordsService
~~~~~~~~~~~~~~~~~~~~~

.. container:: table-row

    Service
         OrphanRecordsService

    Description
          Finds (and fixes) all records that have an invalid / deleted page ID
    
    Source
         \\TYPO3\\CMS\\Lowlevel\\Command\OrphanRecordsCommand::class