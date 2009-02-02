/*

	Context Listener
	  Starts up when app is deployed
	  Creates the socket for web apps to connect to
	
	$Author: myudkowsky $
	$Date: 2006-07-19 10:32:06 -0500 (Wed, 19 Jul 2006) $
	$Id: PushServer.java 82 2006-07-19 15:32:06Z myudkowsky $
	$Revision: 82 $
	
*/

package push2web ;

import java.net.*;
import java.io.*;
import javax.servlet.*;

public class PushServer implements ServletContextListener
{

	int PortNumber ;
	String PortNumberString = "PublicPort" ;
	ServerSocket serverSocket ;
	ListenForConnections clientListen ;


	public void contextInitialized (ServletContextEvent event)
	{
	
		
		ServletContext sc = event.getServletContext();
		
		PortNumber = Integer.parseInt(sc.getInitParameter(PortNumberString)) ;
		
		// make socket for browsers to connect to
		
		try {
			serverSocket = new ServerSocket(PortNumber);
		} catch (IOException e) {
			sc.log("Unable to start socket on port: " + PortNumber );
			System.exit(1);
		}
		
		// spawn thread to listen to this socket
		clientListen = new ListenForConnections(serverSocket, sc ) ;
		clientListen.start() ;
		
		sc.log("PushServer:  init successful" ) ;
	}

	public void contextDestroyed(ServletContextEvent event)
	{
		clientListen.interrupt() ;
	}
	
}
