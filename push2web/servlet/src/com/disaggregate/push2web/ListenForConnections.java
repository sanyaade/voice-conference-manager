/*

	Connection Listener
	  Listens for incoming connections
	  spawns handler to those connections (?)
	
	$Author: myudkowsky $
	$Date: 2006-07-19 10:32:06 -0500 (Wed, 19 Jul 2006) $
	$Id: ListenForConnections.java 82 2006-07-19 15:32:06Z myudkowsky $
	$Revision: 82 $
	
*/

package push2web ;

import java.net.*;
import java.io.*;
import javax.servlet.*;
import javax.servlet.http.*;

public class ListenForConnections extends Thread {

	ServerSocket serverSocket ;
	Socket clientSocket ;
	ServletContext sc ;
	Socket currentSockets[] = new Socket[20] ;

	
	public ListenForConnections (ServerSocket s, ServletContext c ) {
	
		serverSocket = s ;
		sc = c ;

		// init array of sockets
		for (int i = 0 ; i < currentSockets.length ; i++ ) {
			currentSockets[i] = null ;
		}

		// place array where servlets can find them
		sc.setAttribute("currentSockets", currentSockets);
		
		sc.log("ListenForConnections: init successful" );
	}
	
	public void run() {
	
		try {
			serverSocket.setSoTimeout(5000);
		} catch (SocketException e) {
			sc.log("Unable to set timeout time");
			System.exit(1);				// better to exit than pend indefinitely
		}
		
		// pend here indefinitely waiting for client connections
			
		while (true) {	
			try {
				clientSocket = serverSocket.accept();
			} catch (SocketTimeoutException e) {
				continue ;
			} catch (IOException e) {
				sc.log("Unable to accept socket connection");
				System.exit(1);
			}
			
			if (this.interrupted()) {
				stopAllConnections() ;
				break ;
			}

			// create listener, writer for this connection
			sc.log("LFC: new connection") ;
			HandleSocket hs = new HandleSocket(sc, clientSocket ) ;
			
			// this *must* be fixed to handle multi-browser environment!
			sc.setAttribute("currentSocketHandler", hs); 

			// use writer: tell far side they are connected
			try {
				hs.writeOne(clientSocket, "Status;Connected\n");
			} catch (IOException e) {
				sc.log("Unable to write init connection to socket");
				System.exit(1) ;	// generate an error we will see
			}

			// tmp: place sockets onto list
			// eventually: place handler threads onto list
			// place this client socket into a list of client sockets
			// find first null in list of sockets, place socket there
			for (int i = 0 ; i< currentSockets.length; i++) {
				if (currentSockets[i] == null)
				{
					currentSockets[i] = clientSocket ;
					sc.setAttribute("currentSockets", currentSockets);
					break ;
				}
			}
		}
	}
	
	private void stopAllConnections() {
	
		// when app goes down, close sockets.
		for (int i = 0 ; i<currentSockets.length; i++) {
			if (currentSockets[i] != null)
			{
				try {
					currentSockets[i].close();
				}
				catch (IOException e) {
					sc.log("Did not close particular socket");
				}
			}
			
		}
		try	{
			serverSocket.close();
		}
		catch (IOException e) {
			sc.log("did not close main socket");
		}
	}
	
}