.. include:: ../Includes.txt


.. _extensionConfiguration:

=======================
Extension Configuration
=======================

Target group: **Developers, Integrators**

Properties
~~~~~~~~~~

enableBackendModule
"""""""""""""""""""

.. container:: table-row

    Property
         enableBackendModule

    Data type
         boolean (default: true)

    Description
         (De)activate backend module

enableToolbarItem
"""""""""""""""""

.. container:: table-row

    Property
         enableToolbarItem

    Data type
         boolean (default: true)

    Description
         Extend TYPO3 toolbar with cleanup tools in dry-run mode

enableAfterDatabaseOperationsHook
"""""""""""""""""""""""""""""""""

.. container:: table-row

    Property
         enableAfterDatabaseOperationsHook

    Data type
         boolean (default: false)

    Description
         Clean up flexform of a content element after database operations

enablePreviewRenderer
"""""""""""""""""""""

.. container:: table-row

    Property
         enablePreviewRenderer

    Data type
         boolean (default: true)

    Description
         Show hint and cleanup button in content elements if their flexform is not valid

logLifetimeOptions
"""""""""""""""""""""

.. container:: table-row

    Property
         logLifetimeOptions

    Data type
         string (default: 1 day, 1 week, 1 month, 3 months, 6 months, 1 year)

    Description
         Selectable options to deleted log entries in history module