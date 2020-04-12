.. include:: ../Includes.txt


.. _configuration:

=============
Configuration
=============

Target group: **Developers, Integrators**

After installation, no configuration is required to make the extension work.

.. _extensionConfiguration:

Extension Configuration
-----------------------

Properties
~~~~~~~~~~

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

enableDrawItemHook
""""""""""""""""""

.. container:: table-row

    Property
         enableDrawItemHook

    Data type
         boolean (default: true)

    Description
         Show hint and cleanup button in content elements if their flexform is not valid

.. _typoscriptConfiguration:

TypoScript Configuration
------------------------

TypoScript is included by default, no static template is needed.

Service configuration
~~~~~~~~~~~~~~~~~~~~~
   
.. code-block:: typoscript

   module.tx_splcleanuptools {
       services {
           SPL\SplCleanupTools\Service\CleanFlexFormsService {
               enable = 1
               additionalUsage {
                   schedulerTask = 1
                   toolbar = 1
               }
       
               mapping {
                 parameter {
                     pid = int
                     depth = int
                     dryRun = bool
                 }
              }
           }
       }
   }

Properties
~~~~~~~~~~

enable
""""""

.. container:: table-row

    Property
         enable

    Data type
         boolean

    Description
         (De)activate service

additionalUsage
"""""""""""""""

.. container:: table-row

    Property
         additionalUsage

    Data type
         array

    Description
         Settings of further usages

additionalUsage.schedulerTask
"""""""""""""""""""""""""""""

.. container:: table-row

    Property
         additionalUsage.schedulerTask

    Data type
         boolean

    Description
         (De)activate service in scheduler task

additionalUsage.toolbar
"""""""""""""""""""""""

.. container:: table-row

    Property
         additionalUsage.toolbar

    Data type
         boolean

    Description
         (De)activate service in toolbar

mapping
"""""""

.. container:: table-row

    Property
         mapping

    Data type
         array

    Description
         fallback configuration of parameter types, if no type can be determined

Example
~~~~~~~
   
.. code-block:: typoscript

   module.tx_splcleanuptools {
       services {
           Vendor\MyExtension\Service\MyService {
               enable = 1
               additionalUsage {
                   schedulerTask = 0
                   toolbar = 0
               }
       
               mapping {
                 parameter {
                     myFirstVar = integer
                     mySecondVar = boolean
                 }
              }
           }
       }
   }