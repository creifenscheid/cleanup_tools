#
# Table structure for table 'tx_splcleanuptools_domain_model_log'
#
CREATE TABLE tx_splcleanuptools_domain_model_log (
	execution_context tinyint(1) DEFAULT '0',
	service varchar(255) DEFAULT NULL,
	parameters text DEFAULT '' NOT NULL,
	state tinyint(1) DEFAULT '0',
	messages int(11) DEFAULT '0' NOT NULL
);

#
# Table structure for table 'tx_splcleanuptools_domain_model_log_message'
#
CREATE TABLE tx_splcleanuptools_domain_model_log_message (
	log int(11) DEFAULT '0' NOT NULL,
	message text,
	local_lang_key varchar(255) DEFAULT NULL,
	local_lang_arguments mediumtext DEFAULT '' NOT NULL
);