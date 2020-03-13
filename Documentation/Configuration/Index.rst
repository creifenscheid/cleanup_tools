.. include:: ../Includes.txt


.. _configuration:

=============
Configuration
=============

Target group: **Developers, Integrators**

After installation, no configuration is required to make the extension work.

Extension configuration
=======================
The extension configuration is used to enable/disable the following options:
- Toolbar item: Extend TYPO3 toolbar with cleanup tools (default: enabled)
- AfterDatabaseOperations hook: Clean up flexform of content element after database operations (default: disabled)
- DrawItem-Hook: Show hint and cleanup button in content elements if their flexform is not valid (default: enabled)

TypoScript configuration
========================
The extension comes along with TypoScript which is included by default, so there is no static template to include.


Settings
********
.. code-block:: typoscript
   module.tx_splcleanuptools {
      settings {
        localizationFile =
        globalExcludes =
      }
   }

+-----------------------+-------------------------------------------------------------------------------------------------------+
| localizationFile      |  Translation file for service related labels, e.g. in BE module or in the toolbar                     |
+-----------------------+-------------------------------------------------------------------------------------------------------+
| globalExcludes        |  Functions that are globally excluded from direct use, e.g. helper function, base function etc.       |
+-----------------------+-------------------------------------------------------------------------------------------------------+

.. _configuration-typoscript:

TypoScript Reference
====================

Possible subsections: Reference of TypoScript options.
The construct below show the recommended structure for
TypoScript properties listing and description.

When detailing data types or standard TypoScript
features, don't hesitate to cross-link to the TypoScript
Reference as shown below.


See `Hyperlinks & Cross-Referencing <https://docs.typo3.org/typo3cms/HowToDocument/WritingReST/Hyperlinks.html>`
for information about how to use cross-references.

See the :file:`Settings.cgf` file for the declaration of cross-linking keys.
You can add more keys besides tsref.


