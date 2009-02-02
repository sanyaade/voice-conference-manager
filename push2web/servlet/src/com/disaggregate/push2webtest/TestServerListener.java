/*

	Context Listener
	  Starts up when app is deployed
	  Creates the socket for web apps to connect to
	
	$Author: myudkowsky $
	$Date: 2006-06-05 19:45:04 -0500 (Mon, 05 Jun 2006) $
	$Id: TestServerListener.java 51 2006-06-06 00:45:04Z myudkowsky $
	$Revision$
	
*/

package push2web ;

import java.net.*;
import java.io.*;
import javax.servlet.*;

public class TestServerListener implements ServletContextListener
{

	public void contextInitialized(ServletContextEvent event)
	{
	
		ServletContext sc = event.getServletContext();
		int PortNumber = Integer.parseInt(sc.getInitParameter("PortNumber")) ;
		
		System.out.println("hello, world") ;
	}

	public void contextDestroyed(ServletContextEvent event)
	{
		System.out.println("Goodbye, world") ;
	}

}
