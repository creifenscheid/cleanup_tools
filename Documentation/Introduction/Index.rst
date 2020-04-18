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
* toolbar to run services in dry-run mode
* scheduler task
* drawItem hook to run cleanFlexFormsService
* afterDatabaseOperations hook to run cleanFlexFormsService

Cleanup services are registered in typoscript.
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
         
LostFilesService
~~~~~~~~~~~~~~~~~~~~~

.. container:: table-row

    Service
         LostFilesService

    Description
          Finds files within uploads/ which are not needed anymore
    
    Source
         \\TYPO3\\CMS\\Lowlevel\\Command\LostFilesCommand::class
         
OrphanRecordsService
~~~~~~~~~~~~~~~~~~~~~

.. container:: table-row

    Service
         OrphanRecordsService

    Description
          Finds (and fixes) all records that have an invalid / deleted page ID
    
    Source
         \\TYPO3\\CMS\\Lowlevel\\Command\OrphanRecordsCommand::class