.. include:: ../Includes.txt


.. _developer:

================
Developer Corner
================

Target group: **Developers**

Developing your own service
---------------------------

1. Extend \\ChristianReifenscheid\\CleanupTools\\Services\\AbstractCleanupService
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

The return has to be a flash message object.
This can be easly done by using the method "createFlashMessage()".
You can set the severity level, a message and a headline.
All these are optional, by default a success message is created.

On calling a cleanup service, a log entity is created and passed automatically to the called service.
With the following methods, log messages can be added to the log:

* addMessage(string $message) - adds a log message with the given message to the log
* addLLLMessage(string $key, array $arguments) - adds a log message based on localization key, arguments are optional
   
4. Localization
~~~~~~~~~~~~~~~

Service related localizations are set in locallang_services.xlf.

You can create your own localization file to implement your service.
Therefor:

* create your localization file
* include localization nodes for all configured services
* set your localization file in typoscript

.. code-block:: typoscript

   module.tx_cleanuptools {
       settings {
           localizationFile = LLL:EXT:MyExtension/Resources/Private/Language/locallang_custom_services.xlf
       }
   }

Localization key specifications:

* Service description: description.serviceName
* Parameter form label: label.parameterName

.. code-block:: xlfxml

   <!-- DESCRIPTION -->
   <trans-unit id="description.myService">
        <source>My service can do magic</source>
    </trans-unit>
    
   <!-- PARAMETER -->
    <trans-unit id="label.myProperty">
        <source>My property</source>
    </trans-unit>


5. Example
~~~~~~~~~~

.. code-block:: php

   <?php
   namespace Vendor\MyExtension\Service;

   class MyService extends \ChristianReifenscheid\CleanupTools\Service\AbstractCleanupService
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
            return $this->createFlashMessage([FlashMessage::INFO], [$message], [$headline]);
        }
   }

Register your own service
-------------------------

Service registration is done with typoscript (see :ref:`typoscriptConfiguration`)