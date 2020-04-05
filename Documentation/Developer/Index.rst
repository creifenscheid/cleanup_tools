.. include:: ../Includes.txt


.. _developer:

================
Developer Corner
================

Target group: **Developers**

H2: Developing your own service

1. Extend \SPL\SplCleanupTools\Services\AbstractCleanupService

What you get:
- class var $dryRun (default: true)
- class var $log
- method addMessage() to create a log message which is added to the log entry of each run
- method addLLLMessage() to create a log messsge based on a localization key

2. Set up required execute function with your needs

Every registered service has to provide a function named „execute“. Otherwise your service is not added to the backend module or any other execution context.

3. Setting up your service
If your service requires configurable parameters to run, e.g. depth or pageId, be sure to define them as class vars. If you don’t provide default values, the fields are mandatory in the backend module, otherwise they are optional. 

All class vars are parsed to setup up the service form in the backend module dynamically. 
The corresponding form field is based on the var type definition.
 
Possible return types:
- bool: true|false - triggers an equivalent flash message
- int: 0 - triggers an info flash message about how many entries will be affected by running the service - used for dry-runs
- string: xxx - triggers an info flash message with the returned string.

H2: Register your own service
Service registration is done with typoscript (see [link])