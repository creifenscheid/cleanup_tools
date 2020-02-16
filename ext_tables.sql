#
# Table structure for table 'tx_splcleanuptools_domain_model_log'
#
CREATE TABLE tx_splcleanuptools_domain_model_log (
	utility varchar(255) DEFAULT NULL,
	action varchar(255) DEFAULT NULL,
	backups varchar(255) DEFAULT '' NOT NULL
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