#
# Table structure for table 'tx_splcleanuptools_domain_model_log'
#
CREATE TABLE tx_splcleanuptools_domain_model_log (
	processing_context tinyint(1) DEFAULT '0',
	service varchar(255) DEFAULT NULL,
	action varchar(255) DEFAULT NULL,
	state tinyint(1) DEFAULT '0',
	backups int(11) DEFAULT '0' NOT NULL
);

#
# Table structure for table 'tx_splcleanuptools_domain_model_backup'
#
CREATE TABLE tx_splcleanuptools_domain_model_backup (
	log int(11) DEFAULT '0' NOT NULL,
	original_uid int(11) unsigned DEFAULT NULL,
	table varchar(255) DEFAULT NULL,
	data text DEFAULT NULL
);