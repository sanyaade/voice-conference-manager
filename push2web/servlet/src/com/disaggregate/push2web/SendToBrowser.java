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
import javax.servlet.http.*;


public class SendToBrowser extends HttpServlet {

	SendToBrowserFormat FormatData = null ;
	ServletContext sc ;
	HandleSocket hs ;
	
	
	public void init () {
	
		// what is our context?
		sc = getServletContext() ;
		
		// Current socket-handling routine
		// NOTE: *must* be fixed to handle multi-socket systems!!
		hs = (HandleSocket) sc.getAttribute("currentSocketHandler") ;
	
		FormatData = new SendToBrowserFormat() ;
		
	}

	public void doGet(HttpServletRequest request,
					HttpServletResponse response)
		throws IOException, ServletException {
		
		doData ( request, response ) ;
	}
	
	
	public void doPost(HttpServletRequest request,
					HttpServletResponse response)
		throws IOException, ServletException {
		
		try {
			doData ( request, response ) ;
		} catch (Exception e) {
			log("doPost: it blew up");
		}

	}
	
	/* Actual handler */

	private void doData (HttpServletRequest request,
					HttpServletResponse response)
		throws IOException, ServletException {
		
		// Get variables from "request"
		Enumeration nameList = null ;
		HashMap valueList = new HashMap() ;	// do not need more than 16 entries, the default
		String paramName ;
		
		try {
			nameList = request.getParameterNames() ;
		} catch (NullPointerException e) {
			log("doData: null pointer exception") ;
			System.exit(1);
		}

		while  ( nameList.hasMoreElements() ){
			
			paramName = (String) nameList.nextElement() ;
			// put key/value pairs into map
			// valueList.put(paramName, request.getParameter(paramName) ) ;
			try {
				valueList.put(paramName, request.getParameter(paramName) ) ;
				// log("paramName: " + paramName + " , paramValue: " + request.getParameter(paramName) ) ;
			} catch (NullPointerException e) {
				log("null pointer exception(2)");
				System.exit(1);
			}
			
		}

		// Call the specified handler
		String outString = null ;
		try {
			outString = FormatData.format(valueList) ;
		} catch (NullPointerException e) {
		log("null pointer exception(3)");
			System.exit(1);
		}
		
		// write to the socket
		
		try {
			hs.write(outString) ;
		} catch (NullPointerException e) {
			log("null pointer exception(4)");
			System.exit(1);
		}
		
		
		// NOTE: what about response to CCXML interpreter?

	}
	
	public void setDefaultDataHandler (SendToBrowserFormat fd ) {
	
		FormatData = fd ;
	}
		
}
