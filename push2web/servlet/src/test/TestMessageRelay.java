/*

	Test system

	Accept incoming data from a Java Server Page
	Retransmit data via HTTP to test server
	
	Should pass XML, but we'll do what we can right now

	Copyright (c) 2006 by M. Yudkowsky, Disaggregate (http://www.Disaggregate.com)

	$Author: myudkowsky $
	$Date: 2006-06-07 09:25:34 -0500 (Wed, 07 Jun 2006) $
	$Id: TestMessageRelay.java 54 2006-06-07 14:25:34Z myudkowsky $
	$Revision: 43 $
*/


import java.net.*;
import java.io.*;
import javax.servlet.*;
import javax.servlet.http.*;


public class TestMessageRelay extends HttpServlet {


    public void doGet(HttpServletRequest request,
                      HttpServletResponse response)
        throws IOException, ServletException {
	
		// first thing: ackknowledge!
		response.setContentType("text/plain");
		PrintWriter pageout = response.getWriter();
		pageout.println("starting") ;

		// test params
		// these need to be in confg file
		
		int sessionid = 42 ;
		String phoneDest = "17737648727";
		String eventname = "zulu" ;
		int PortNumber = 15334 ;
		String basefile = "/cgi-bin/testout.cgi" ;
		
		String post ;


		
		// at this point we should ordinarily spawn a thread for each client.
		// however, in the meantime, we proceed
		
		// just send stuff via URL
		// not via socket connection
		
		
		
		// our Python CGI server does not accept POST variables
		// use GET instead of POST
		// basefile += "?" ;
		
		post = "sessionid=" ;
		String sess = URLEncoder.encode( Integer.toString(sessionid), "UTF-8" ) ;
		post += sess ;
		post += "&" ;
		post += "phoneDest=" ;
		post +=  URLEncoder.encode(phoneDest,  "UTF-8" ) ;
		
/*		post += "&" ;
		post += "eventname=" + eventname ;
		post += "&" ;
		post += "phoneDest=" + phoneDest ;*/
		
		// create URL object
		
		URL report = new URL ("http", "bagpipes", PortNumber /*80*/ , basefile /*"/cgi-bin/users/1000/cgi-bin/testout.cgi"*/) ;
// 		URL report = new URL ("http://java.sun.com/cgi-bin/backwards" ) ;
		HttpURLConnection connection = (HttpURLConnection) report.openConnection();
		
		// Set for POST output
		connection.setDoOutput(true);
		connection.setRequestMethod("POST");
 		// create output writer
 		PrintWriter out = new PrintWriter(connection.getOutputStream());
 		// Write. Note thet connection.connect() is *not* used!
		out.println(post) ; 
		out.flush() ;
		out.close() ;

		// some debugging 
		pageout.println("connected to " + report.getHost() ) ;
		pageout.println("on port " + report.getPort() ) ;
		pageout.println("looking for " + report.getFile() ) ;
		pageout.println("sent as POST: " + post ) ;
		
		
		// read from URL
		
		BufferedReader in = new BufferedReader( new InputStreamReader( connection.getInputStream()) );
		
		String output ;
		
		while ( (output = in.readLine()) != null )
		{
			pageout.println(output) ;
		}
		
		in.close() ;
		
 		// successful end to output
		pageout.println("success") ;
		pageout.close();
/*		clientSocket.close();
		serverSocket.close();*/
	}
}
