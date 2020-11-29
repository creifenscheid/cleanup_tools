.. include:: ../Includes.txt


.. _developer:

================
Developer Corner
================

Target group: **Developers**

Developing your own service
---------------------------

1. Extend \\CReifenscheid\\CleanupTools\\Services\\CleanupService\\AbstractCleanupService
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

What you get:

* class var $dryRun (default: true)
* class var $log
* method addMessage() to create a log message which is added to the log entry of each run
* method addLLLMessage() to create a log message based on a localization key

2. Set up required execute function with your needs
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Every registered service has to provide a function named „execute“. Otherwise your service is not added to the backend module or any other execution context.

3. Setting up your service
~~~~~~~~~~~~~~~~~~~~~~~~~~

If your service requires configurable parameters to run, e.g. depth or pageId, be sure to define them as class property. If you don’t provide default values, the fields are mandatory in the backend module, otherwise they are optional. 
Make sure to define a setter function for each property.

All properties are parsed to set up the service form in the backend module automatically. 
The corresponding form field is based on the var type definition.

The return has to be a flash message object.
This can be easly done by using the method "createFlashMessage()".
You can set the severity level, a message and a headline.
All these are optional, by default a success message is created.

On calling a cleanup service, a log entity is created and passed automatically to the called service if it's not processed in dry run mode.
With the following methods, log messages can be added to the log:

* addMessage(string $message) - adds a log message with the given message to the log
* addLLLMessage(string $key, array $arguments) - adds a log message based on localization key, arguments are optional
   
4. Localization
~~~~~~~~~~~~~~~

You can create your own localization file for your implemented service.
Therefor:

* create your localization file
* register your localization file in ext_localconf.php of your extension

.. code-block:: php

    \CReifenscheid\CleanupTools\Utility\ConfigurationManagementUtility::addLocalizationFilePath('EXT:my_extension/Resources/Private/Language/locallang_myfile.xlf');
    

Localization key specifications:

* Service description: description.serviceName
* Extended service description for info module: description.extended.serviceName
* Parameter form label: label.parameterName
* Parameter description for info module: description.parameterName

.. code-block:: xlfxml

   <!-- DESCRIPTION -->
   <trans-unit id="description.myService">
        <source>My service can do magic</source>
    </trans-unit>
    <trans-unit id="description.extended.myService">
        <source>My service can do magic</source>
    </trans-unit>
    
   <!-- PARAMETER -->
    <trans-unit id="label.myProperty">
        <source>My property</source>
    </trans-unit>
    <trans-unit id="description.myProperty">
        <source>My property is used for</source>
    </trans-unit>


5. Example
~~~~~~~~~~

.. code-block:: php

   <?php
   namespace Vendor\MyExtension\Service;

   class MyService extends \CReifenscheid\CleanupTools\Service\CleanupService\AbstractCleanupService
   {
        /**
         * my first class var
         *
         * @var int
         */
        protected $myFirstVar;
        
        /**
         * Set my first var
         */
        public function setMyFirstVar(int $myFirstVar)
        {
            $this->myFirstVar = $myFirstVar;
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

You can easliy register your cleanup service within ext_localconf.php of your extension by using \CReifenscheid\CleanupTools\Utility\ConfigurationManagementUtility::addCleanupService()


.. code-block:: php

   \CReifenscheid\CleanupTools\Utility\ConfigurationManagementUtility::addCleanupService(
        'myCleanupService', // identifier
        \Vendor\MyExtension\Service\CleanupService\MyService::class, // class
        true, // service enabled in scheduler task
        true, // service enabled in toolbar
        true // enable
    );
