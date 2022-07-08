CREATE TABLE sys_file_reference (
    tx_b8motor_as_responsive tinyint(1) unsigned DEFAULT 0 NOT NULL,
    tx_b8motor_component_type varchar(64) DEFAULT '' NOT NULL,
    tx_b8motor_component_style varchar(64) DEFAULT '' NOT NULL,
);

#
# Table structure for table 'tx_b8motor_breakpoint_images'
#
CREATE TABLE tx_b8motor_breakpoint_images (
    uid int(11) unsigned NOT NULL auto_increment,
    cid int(11) unsigned DEFAULT 0 NOT NULL,
    img_id int(11) unsigned DEFAULT 0 NOT NULL,
    deleted tinyint(1) unsigned DEFAULT 0 NOT NULL,
    file varchar(255) DEFAULT '' NOT NULL,
    width int(11) unsigned DEFAULT 0 NOT NULL,
    height int(11) unsigned DEFAULT 0 NOT NULL,
    PRIMARY KEY (uid),
    KEY uid (uid),
    KEY cid (cid),
);

