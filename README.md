## TYPO3 Extension "Cleanup Tools"

### Purpose
The core extension [**"lowlevel"**][1] provides some great features, e.g. checking and cleaning flexforms.
Inspired by these tools, "Cleanup tools" provides various possibilities to check and clean up your TYPO3 installation.
It is shipped with adaptions of the lowlevel tools and can be extended by your own services easily.

### Tools
#### Backend module
* admins only
* run cleanup services
* history of cleanup runs
* information about services and their properties

#### Scheduler task
* run enabled services as task

#### Hooks
* preview renderer: shows notification if a content elements flexform is not valid and provides a button to clean the flexform
* after database operations: checks flexform of content element after saving and cleans it up, if it is not valid

#### Dashboard
* show dry run results of services to provide information about your installation

#### Toolbar
* admins only
* quick access to dry-run result of single service

### Configuration
* hooks can be disabled/enabled in extension configuration

\CReifenscheid\CleanupTools\Utility\ConfigurationManagementUtility
* register your own service
* register localizations file which include information about your service
* enable/disable/remove already registered services
* enable/disable usage of services in scheduler task or toolbar


### Support
I don't want your money or anything else.
I am doing this for fun, with heart and to improve my coding skills.
Constructive critisismn is very welcome.
If you want to contribute, feel free to do so.
Thank you!

[1]: https://github.com/TYPO3/TYPO3.CMS/tree/master/typo3/sysext/lowlevel/Classes/Command
