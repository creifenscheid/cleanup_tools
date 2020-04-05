.. include:: ../Includes.txt


.. _introduction:

============
Introduction
============

Target group: **Administrators**

What does it do?
================

This extension ports command line functions of core extension 'lowlevel' into the backend to clean up your TYPO3 installation.

It provides the following features:
- backend module to run services
- history module to take a look on previous runs 
- toolbar to run services in dry-run mode
- scheduler task to run services
- drawItem hook to run cleanFlexFormsService
- afterDatabaseOperations hook to run cleanFlexFormsService

Cleanup services are registered in typoscript.
Therefor it is possible to extend the extension with your own services easily.
