/*

	Send to Browser
	
	Used by CCXML to send data to browser
	
	1. Accept variables from incoming
	2. Format them via specified handler
	3. Hand them off to port

	Should pass XML, but we'll do what we can right now
	
*/

package push2web ;

import java.net.*;
import java.io.*;
import java.util.* ;
import javax.servlet.*;

// by extending HttpServlet, we can log messages

public class SendToCCXML {

	SendToCCXMLFormat FormatData = null ;
	Log logger ;

	
	public SendToCCXML(ServletContext srvc) {
	
		FormatData = new SendToCCXMLFormat(srvc) ;
		logger = new Log(srvc) ;
		logger.log("SendToCCXML: init successful" ) ;
	}

	public void write(String s) {
	
		HttpURLConnection connection = null ;
	
		logger.log ("STCCXML - received " + s );
	
		// string came from Browser. Transform into correct URL
		URL outURL = FormatData.format(s) ;
		logger.log ("STCCXML - URL is " + outURL.toString() ) ;
	
		try {
			// get URL connection
			connection = (HttpURLConnection) outURL.openConnection() ;
		} catch (NullPointerException e) {
			logger.log("null pointer exception(3)");
			// System.exit(1);
		}
		catch (IOException e) {
			logger.log ("io exception (4)") ;
			// System.exit(1) ;
		}

		try {
			// connect to this URL -- that sends the signal to the CCXML interpreter
			connection.connect() ;
		} catch (NullPointerException e) {
			logger.log("null pointer exception(5)");
			// System.exit(1);
		}
		catch (IOException e) {
			logger.log ("io exception (6)") ;
			// System.exit(1) ;
		}
		
		try {
			connection.getContent() ;
		} catch (IOException e) {
			logger.log ("io exception (8)") ;
			// System.exit(1) ;
		}
		
	
		try {
			// get the status
			// connection.getResponseMessage() ;
			
			// disconnect and dispose of connection 
			connection.disconnect() ;

		} catch (NullPointerException e) {
			logger.log("null pointer exception(7)");
			// System.exit(1);
		}
		
	}
	
	public void setDefaultDataHandler (SendToCCXMLFormat cfd ) {
	
		FormatData = cfd ;
	}

}
