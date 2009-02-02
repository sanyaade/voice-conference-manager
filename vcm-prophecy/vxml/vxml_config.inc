<?php

	/*
		Common configuration information for all VoiceXML scripts

	*/


	/*
		Copyright (c) 2004-2006 by M. Yudkowsky, Disaggregate http://www.Disaggregate.com
		See attached license for details and disclaimers

		$Author: myudkowsky $
		$Date: 2006-07-28 13:54:20 -0500 (Fri, 28 Jul 2006) $
		$Id: vxml_config.inc 97 2006-07-28 18:54:20Z myudkowsky $
		$Revision: 97 $
	*/
	
	// configuration items. Includes script names, delimiters, universal timeouts
	
	$delimiter = ";" ;
	
	// grammar files 
	$grammar_location = "http://127.0.0.1:9990/vcm-p2006/vxml/confgrammar.php" ;
	$dataDir = "../data/" ;	// location of files

	// test grammar file
	$contact_file = "../data/vcm.xml" ;
	


?>