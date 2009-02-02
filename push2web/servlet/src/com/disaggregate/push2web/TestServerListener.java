/*

	Context Listener
	  Starts up when app is deployed
	  Tests ability to create context listener
	
	$Author: myudkowsky $
	$Date: 2006-07-19 10:32:06 -0500 (Wed, 19 Jul 2006) $
	$Id: TestServerListener.java 82 2006-07-19 15:32:06Z myudkowsky $
	$Revision: 82 $
	
*/

package push2web ;

import java.net.*;
import java.io.*;
import javax.servlet.*;

public class TestServerListener implements ServletContextListener
{

	ServletContext sc ;

	public void contextInitialized(ServletContextEvent event)
	{
	
		sc = event.getServletContext();
		
		//int PortNumber = Integer.parseInt(sc.getInitParameter("PortNumber")) ;
		
		sc.log("hello, world") ;
	}

	public void contextDestroyed(ServletContextEvent event)
	{
		sc.log("Goodbye, world") ;
	}

}
