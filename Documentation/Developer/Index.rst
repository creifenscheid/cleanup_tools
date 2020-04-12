.. include:: ../Includes.txt


.. _developer:

================
Developer Corner
================

Target group: **Developers**

Developing your own service
---------------------------

1. Extend \\SPL\\SplCleanupTools\\Services\\AbstractCleanupService
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

What you get:

* class var $dryRun (default: true)
* class var $log
* method addMessage() to create a log message which is added to the log entry of each run
* method addLLLMessage() to create a log messsge based on a localization key

2. Set up required execute function with your needs
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Every registered service has to provide a function named „execute“. Otherwise your service is not added to the backend module or any other execution context.

3. Setting up your service
~~~~~~~~~~~~~~~~~~~~~~~~~~

If your service requires configurable parameters to run, e.g. depth or pageId, be sure to define them as class property. If you don’t provide default values, the fields are mandatory in the backend module, otherwise they are optional. 
Make sure that your properties are either public or can be set by a corresponding setter.

All properties are parsed to set up the service form in the backend module automatically. 
The corresponding form field is based on the var type definition.
 
Possible return types:

* bool: true|false - triggers an equivalent flash message
* int: 0 - triggers an warning flash message about how many errors occurred while executing the specific function
* string: xxx - triggers an info flash message with the returned string, used for e.g. dry runs

4. Example
~~~~~~~~~~

.. code-block:: php

   <?php
   namespace Vendor\MyExtension\Service;

   class MyService extends \SPL\SplCleanupTools\Service\AbstractCleanupService
   {
        /**
         * my first class var
         *
         * @var int
         */
        public $myFirstVar = 1000;
   		
        /**
         * my second class var
         * 
         * @var bool
         */
        proteted $mySecondVar = true;
        
        /**
         * Setter
         */
        public function setMySecondVar (bool $mySecondVar) {
            $this->mySecondVar = $mySecondVar;
        }
   		
        /**
         * Execute function
         */
        public function execute() {
            // insert your magic here
            return true|false|[int]|[string];
        }
   }

Register your own service
-------------------------

Service registration is done with typoscript (see :ref:`typoscriptConfiguration`)