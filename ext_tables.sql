#
# Table structure for table 'tx_splcleanuptools_domain_model_log'
#
CREATE TABLE tx_splcleanuptools_domain_model_log (
	execution_context tinyint(1) DEFAULT '0',
	service varchar(255) DEFAULT NULL,
	method varchar(255) DEFAULT NULL,
	state tinyint(1) DEFAULT '0'
);