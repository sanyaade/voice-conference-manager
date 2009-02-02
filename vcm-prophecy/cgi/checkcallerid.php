<?php

	/*

		Check caller ID
		
		Parse incoming variables
		
		Respond with:
			valid = True, False	 -- is the caller authorized to use service?
			conference grammar	 -- where do we look for caller's list of names?
			
		Response is via an HTTP "CCXML10.send" message

	*/


	/*
		Copyright (c) 2004-2006 by M. Yudkowsky, Disaggregate http://www.Disaggregate.com
		See attached license for details and disclaimers

		$Author: myudkowsky $
		$Date: 2006-07-28 13:38:32 -0500 (Fri, 28 Jul 2006) $
		$Id: checkcallerid.php 96 2006-07-28 18:38:32Z myudkowsky $
		$Revision: 25 $
	*/
	
	// configuration items. Includes script names, delimiters, universal timeouts
	
	require("cgi_config.inc");
?>

<?php

// who we are authenticating
$user			= $_REQUEST["caller"] ;

// where to send results
$returnAddressURL		= $_REQUEST["returnAddressURL" ] ;
$returnAddressSession	= $_REQUEST["returnAddressSession"] ;

// look into XML file of authorized users
// first, init variables. If not in list, this is what we will return
$retval = "False" ;
$grammar_menu = "None" ;

$list = simplexml_load_file($authorized_users) ;
foreach($list->user as $current)
{
	if ($current->username == $user)
	{
		$retval = "True" ;
		$grammar_menu = $current -> addressbook ;
		break ;
	}
}

// check for a default user
if ( ($retval == "False") && ($default_user != "") ) {
	$retval = "True" ;
	$grammar_menu = $default_user;
}

// create query string

$query = "sessionid=" . urlencode($returnAddressSession) ;

$query .= "&eventname=calleriddata" ;

$query .= "&valid=" . urlencode($retval) ;

$query .= "&grammar_menu=" . urlencode($grammar_menu) ;

$query .= "&thisConnectionID=" . urlencode($_REQUEST["thisConnectionID"]) ;

$returnAddress = $returnAddressURL . "?" . $query ; 

// Send this info back to whoever called us
// multiple attempts to ping in case of network problems

$count = 0 ;

while (True)
{
	try {
	
		$remote = fopen( $returnAddress, "r" ) ; // or throw new Exception("not opened") ;
		fclose($remote) ;
		break ; 
	}
	catch (Exception $e) 
	{
		$count += 1 ;
		print $count ;
		if ( $count <= $MaxHTTPErrors ) continue; else break ;
	}
}


// Finished. Print a success message as web page result.

// print "Content-type: text/plain\n\n"  ;
print "\nsuccess\n" ;

?>
