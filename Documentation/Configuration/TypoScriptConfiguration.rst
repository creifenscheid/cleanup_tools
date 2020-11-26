.. include:: ../Includes.txt


.. _typoscriptConfiguration:

========================
TypoScript Configuration
========================

Target group: **Developers, Integrators**

TypoScript is included by default, no static template is needed.

Settings
~~~~~~~~

.. code-block:: typoscript

   module.tx_cleanuptools {
       settings {
           localizationFilePaths { 
               10 = LLL:EXT:cleanup_tools/Resources/Private/Language/locallang_properties.xlf
           }
        # comma separated list of time spans
        logLifetimeOptions = 1 day, 1 week, 1 month, 3 months, 6 months, 1 year
       }
   }
   
Properties
~~~~~~~~~~

localizationFilePaths
"""""""""""""""""""""

.. container:: table-row

    Property
         localizationFilePaths

    Data type
         array

    Description
         Paths to localization files with service related values. Paths will be resolved automatically
         
logLifetimeOptions
""""""""""""""""""

.. container:: table-row

    Property
         logLifetimeOptions

    Data type
         string

    Description
         Comma-separated list of time spans to delete logs (converted by strtotime)

Service configuration
~~~~~~~~~~~~~~~~~~~~~
   
.. code-block:: typoscript

   module.tx_cleanuptools {
       services {
           CReifenscheid\CleanupTools\Service\CleanupService\CleanFlexFormsService {
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

   module.tx_cleanuptools {
       services {
           Vendor\MyExtension\Service\CleanupService\MyService {
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