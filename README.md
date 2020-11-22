# TYPO3 Extension "Cleanup Tools"

### Purpose
The core extension [**"lowlevel"**][1] provides some great features, e.g. checking and cleaning flexforms.
Inspired by these tools, "Cleanup tools" provides various possibilities to check and clean up your TYPO3 installation.
It is shipped with adaptions of the lowlevel tools and can be extended by your own services easily.

### Tools
#### Backend module
- admins only
- run cleanup services
- history of cleanup runs
- information about services and their properties

#### Scheduler task
- run enabled services as task

#### Hooks
- preview renderer: shows notification if a content elements flexform is not valid and provides a button to clean the flexform
- after database operations: checks flexform of content element after saving and cleans it up, if it is not valid

#### Dashboard
- show dry run results of services to provide information about your installation

#### Toolbar
- admins only
- quick access to dry-run result of single service

### Configuration
- hooks can be dis/enabled in extension configuration
- services can be dis/enabled with typoscript
- service usage in toolbar and scheduler can be dis/enabled with typoscript
- your services can be registered with typoscript

### Release Management
[**Semantic versioning**][2]
* **bugfix updates** (e.g. 1.0.0 => 1.0.1) just includes small bugfixes or security relevant stuff without breaking changes,
* **minor updates** (e.g. 1.0.0 => 1.1.0) includes new features and smaller tasks without breaking changes,
* and **major updates** (e.g. 1.0.0 => 2.0.0) breaking changes wich can be refactorings, features or bugfixes.

### Support
I don't want your money or anything else.
I am doing this for fun, with heart and to get better.
Constructive critisismn is very welcome.
If you want to contribute, feel free to do so.
Thank you!

[1]: https://github.com/TYPO3/TYPO3.CMS/tree/master/typo3/sysext/lowlevel/Classes/Command
[2]: https://semver.org/
