/*

	Format What We Send to Browser
	
	Given Map, format into String

	Should pass XML, but we'll do what we can right now

	$Author: myudkowsky $
	$Date: 2006-07-19 10:32:06 -0500 (Wed, 19 Jul 2006) $
	$Id: SendToBrowserFormat.java 82 2006-07-19 15:32:06Z myudkowsky $
	$Revision: 82 $


*/

package push2web ;

import java.net.*;
import java.io.*;
import java.util.* ;


public class SendToBrowserFormat {

	String DELIM = ";" ;
	
	// List of items sent by CCXML:
	String CCXML_CALLID			= "confName" ;
	String CCXML_UNIQUEID		= "confUniqueName" ;
	String CCXML_NAME			= "destName" ;
	String CCXML_PHONENUMBER	= "destPhone" ;
	String CCXML_REPORT			= "report" ;
	String CCXML_SESSIONID		= "thisSession" ;


	String format (Map m) {
	
		// put into our weird format
		// should move to XML soon!

	
		String retval  ;
		
		retval = "Data" ;
		retval += DELIM ;
		retval += m.get(CCXML_CALLID) ;
		retval += DELIM ;
		retval += m.get(CCXML_UNIQUEID) ;
		retval += DELIM ;
		retval += m.get(CCXML_NAME) ;
		retval += DELIM ;
		retval += m.get(CCXML_PHONENUMBER) ;
		retval += DELIM ;
		retval += m.get(CCXML_REPORT) ;
		retval += DELIM ;
		retval += m.get(CCXML_SESSIONID) ;
	
		
		return retval ; 
		
	}
}
