.. include:: ../Includes.txt


.. _introduction:

============
Introduction
============

Target group: **Administrators**

What does it do?
================

This extension ports command line functions of core extension 'lowlevel' into the backend to clean up your TYPO3 installation.

It implements the following possibilities to execute these methods:

* backend module
* toolbar item
* processDatamap_afterDatabaseOperations hook
* drawItem hook

Cleanup services are registered in typoscript.
Therefor it is possible to extend the extension with your own services easily.
