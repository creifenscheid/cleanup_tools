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

   module.tx_splcleanuptools {
       settings {
           localizationFile = LLL:EXT:spl_cleanup_tools/Resources/Private/Language/locallang_services.xlf
        # comma separated list of time spans
        logLifetimeOptions = 1 day, 1 week, 1 month, 3 months, 6 months, 1 year
       }
   }
   
Properties
~~~~~~~~~~

localizationFile
""""""""""""""""

.. container:: table-row

    Property
         localizationFile

    Data type
         string

    Description
         Path to localization file with service related values
         
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