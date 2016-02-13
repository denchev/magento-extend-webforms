<?php

$installer = $this;
 
$installer->startSetup();
 
$installer->getConnection()
	->addColumn(
		$this->getTable('webforms'),
		'countrybased_pairs',
		'TEXT NOT NULL AFTER `email`'
	)
;
 
$installer->endSetup();

?>