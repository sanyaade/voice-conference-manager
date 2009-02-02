/*

	Format what we receive from browser to send to CCXML interpreter
	
	Given Map, format into String

	Should pass XML, but we'll do what we can right now

	$Author: myudkowsky $
	$Date: 2006-07-06 14:54:31 -0500 (Thu, 06 Jul 2006) $
	$Id: SendToCCXMLFormat.java 74 2006-07-06 19:54:31Z myudkowsky $
	$Revision: 74 $


*/

package push2web ;

import java.net.*;
import javax.servlet.*;


public class SendToCCXMLFormat {

	String splitter = ";" ;
	String DELIM	= "&" ;
	
	// List of items sent by CCXML:
	int CCXML_ACTION			= 3 ;
	int CCXML_SESSIONID		= 1 ;

	String URL_prefix = "http://localhost:9999/CCXML.send?" ;
	
	Log logger ;
	
	public SendToCCXMLFormat (ServletContext srvc) {
		logger = new Log(srvc) ;
	}

	public URL format (String in) {
	
		// take out of our weird format
		// should be XML format one day soon
		
		// Note: we do NOT check to see if the data are prefixed by "Data."
		// when we have more elaborate command sets we may need to filter results and choose formatters
		
		String a[] = in.split(splitter) ;
		String s  ;
		
		s = URL_prefix ;
		
		s += "sessionid=" + a[CCXML_SESSIONID];
		s += DELIM ;
		s += "eventname=" + a[CCXML_ACTION] ;
	
		URL retval = null ;
		
		logger.log("transforming " + s + " to URL" ) ;
		
		try {
			retval = new URL(s) ;
		} catch (MalformedURLException e) {
			logger.log("malformed URL: " + s) ;
			System.exit(1) ;
		}
		
		logger.log(retval.toString()) ;
		
		return retval ; 
		
	}
}
