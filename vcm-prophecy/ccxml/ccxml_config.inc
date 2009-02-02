<?php

	/*
		Configuration options for CCXML scripts
	*/

	/*
		Copyright (c) 2004-2006 by M. Yudkowsky, Disaggregate http://www.Disaggregate.com
		See attached license for details and disclaimers

		$Author: myudkowsky $
		$Date: 2006-07-28 13:54:20 -0500 (Fri, 28 Jul 2006) $
		$Id: ccxml_config.inc 97 2006-07-28 18:54:20Z myudkowsky $
		$Revision: 97 $
	*/
	
	// outbound caller id
	$callerid = "17737648727" ;

	// Number of instances available on platform
	$NumberInstances = 2 ;

	// delimiter for use in sending arrays from script to script
	$delimiter = ";" ;
	
	// CCXML scripts
	$conf_init	= "conf_init.php" ;	// pre-call init (conference object)
	$conf_legs	= "conf_legs.php" ;	// manage each call leg

	// VoiceXML scripts for various announcements for call legs
	$conf_join		= "youarejoining.vxml" ;
	$conf_err		= "goodbye_error.vxml" ;
	$conf_dropped	= "goodbye_dropped.vxml" ;
	$conf_over		= "confcallisover.vxml" ;
	
	// VoiceXML scripts for announcement for attendant
	
	$conf_error		= "conf_error.vxml" ;
	$conf_started	= "conf_started.vxml" ;
	
	// VoiceXML script to select attendees
	$conf_selections	= "conference.php" ;

	// CGI script to check caller id
	$checkCallerID = "http://127.0.0.1:9990/vcm-p2006/cgi/checkcallerid.php" ;
	
	// Our return address URL
	$returnAddressURL = "http://127.0.0.1:9999/CCXML.send" ;
	
	// Error limits
	$MaxNumberErrors = 3 ;
	$MaxRetryErrors = 3 ;
	
	// delays
	$postSessionDelay = "3s" ;		// wait this time after CCXML attendant script exits to start conf object
	$authorizationDelay = "3s" ;	// wait for authorization
	$muteDelay = "500ms" ;			// work-around for unjoin/join issue
	
	// notification of clerk via push2web
	
	$push2web = true ;
	$push2webURL = "http://127.0.0.1:9991/push2web/send.do" ;
	
		// write out the CCXML to send notification
		// this is, in essence, a macro
		// an excellent candidate for a seperate CCXML process that manages <send>
		// but we have limited number of CCXML processes
		function sendPush( $report ) {
			global $push2web, $push2webURL ; 	// bring into local scope
			if ($push2web) {
				echo "<assign name=\"report\" expr=\"'$report'\"/>" ;
				echo "<send data=\"'foo'\" target=\"'$push2webURL'\" targettype=\"url_event\" " ;
				echo "namelist=\"report destName destPhone confName confUniqueName thisSession\"/>" ;
			}
		}
	
		
		


?>