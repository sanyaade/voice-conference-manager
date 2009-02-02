/*

	Context Listener
	  Starts up when app is deployed
	  Creates the socket for web apps to connect to
	
	$Author: myudkowsky $
	$Date: 2006-06-05 19:45:04 -0500 (Mon, 05 Jun 2006) $
	$Id: PushServer.java 51 2006-06-06 00:45:04Z myudkowsky $
	$Revision$
	
*/

package push2web ;

import java.net.*;
import java.io.*;
import javax.servlet.*;

public class PushServer implements ServletContextListener
{

	int PortNumber ;
	Socket clientSocket ;
	ServerSocket serverSocket ;
	Socket currentSockets[] = new Socket[20] ;


	public void contextInitialized(ServletContextEvent event)
	{
	
		ServletContext sc = event.getServletContext();
		int PortNumber = Integer.parseInt(sc.getInitParameter("PortNumber")) ;
		
		System.out.println("hello, world") ;
	}

	public void foo(ServletContextEvent event)
	{
		
		// get port to outside world
		
		ServletContext sc = event.getServletContext();
		int PortNumber = Integer.parseInt(sc.getInitParameter("PortNumber")) ;
		
		// init array of sockets
		
		for (int i = 0 ; i < currentSockets.length ; i++ )
		{
			currentSockets[i] = null ;
		}
		
		// place array where servlets can find them
		sc.setAttribute("currentSockets", currentSockets);

		// make socket for browsers to connect to
		
		try {
			serverSocket = new ServerSocket(PortNumber);
		} catch (IOException e) {
			System.err.println("Unable to start socket on port: " + PortNumber );
			System.exit(1);
		}

		// pend here indefinitely waiting for client connections
		
		while (true)
		{

			try {
				clientSocket = serverSocket.accept();
			} catch (IOException e) {
				System.err.println("Accept failed.");
				System.exit(1);
			}
			
			// tmp: place sockets onto list
			// eventually: place handler threads onto list
			// place this client socket into a list of client sockets
			
			// find first null in list of sockets, place socket there
			for (int i = 0 ; i< currentSockets.length; i++)
			{
				if (currentSockets[i] == null)
				{
					currentSockets[i] = clientSocket ;
					sc.setAttribute("currentSockets", currentSockets);
					break ;
				}
			}
		}
		

	}

	public void contextDestroyed(ServletContextEvent event)
	{
		// when app goes down, close sockets.
		for (int i = 0 ; i<currentSockets.length; i++)
		{
			if (currentSockets[i] != null)
			{
				try
				{
					currentSockets[i].close();
				}
				catch (IOException e)
				{
					System.err.println("Accept failed.");
				}
			}
			
		}
		try	{
			serverSocket.close();
		}
		catch (IOException e) {
			System.err.println("Accept failed.");
		}

	}
}
