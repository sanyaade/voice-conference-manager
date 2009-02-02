/*

	Handle Socket
	  Spawn Threads that handle socket I/O
	  Provide write method to socket
	  Provide registration of object that provides write method and
	   write data to that method

	
	$Author: myudkowsky $
	$Date: 2006-06-05 19:45:04 -0500 (Mon, 05 Jun 2006) $
	$Id: ReadFromWeb.java 51 2006-06-06 00:45:04Z myudkowsky $
	$Revision$
	
*/

package push2web ;

import java.net.*;
import java.io.*;


// Thread. Reads from socket, writes to object with "write" method

class ReadFromWeb implements Runnable {

	BufferedReader in = null ;
	PrintWriter streamHandler = null ; 

	public ReadFromWeb (Socket clientSocket)
		throws IOException {
	
		in = new BufferedReader( new InputStreamReader( clientSocket.getInputStream()) );
	}
	
	public void setHandler (PrintWriter handler) {
		streamHandler = handler ;
	}
	
	public void run() {
		
		String output = null ;
	
		// try to get first line of output
		try {
			output = in.readLine() ;
		}
		catch (IOException e) {
		}

		while ( output != null ) {
			if (streamHandler != null ) {
				streamHandler.write(output) ;
			}
			try {
				output = in.readLine() ;
			}
			catch (IOException e) {
					break ;
			}

		}
	}

}

