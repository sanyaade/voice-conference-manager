<?php

	/*
		Configuration parameters for CGI scripts
	*/

	/*
		Copyright (c) 2004-2006 by M. Yudkowsky, Disaggregate http://www.Disaggregate.com
		See attached license for details and disclaimers

		$Author: myudkowsky $
		$Date: 2006-07-28 13:38:32 -0500 (Fri, 28 Jul 2006) $
		$Id: cgi_config.inc 96 2006-07-28 18:38:32Z myudkowsky $
		$Revision: 25 $
	*/


// max number of errors for HTTP queries
$MaxHTTPErrors = 3 ; 

// location of valid caller list
$dataDir = "../data/" ;
$authorized_users = $dataDir . "callerdata.xml" ;

// default user
//  if caller ID is not functioning, having a default user will 
//  allow system to work. Important for "out-of-box" install
//  if you have not configured SIP phone to provide caller ID.
//  ** use $default_user = "" for a more secure conference server **
// $default_user = "" ;
$default_user = $dataDir . "defaultuser.xml";

?>


