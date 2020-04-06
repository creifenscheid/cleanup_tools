.. include:: ../Includes.txt


.. _configuration:

=========================
TypoScript Configuration
=========================

Target group: **Developers, Integrators**

TypoScript is included by default, no static template is needed.


H2: Service configuration

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
		
Description:
- enable: (de)activate service in general
- additionalUsage: setting for further usages
- schedulerTask: (de)activate service in scheduler task
- toolbar: (de)activate service in toolbar
- mapping: fallback configuration of parameter types, when no type can be ermittelt

 
H2: Register your own service
Copy and paste 