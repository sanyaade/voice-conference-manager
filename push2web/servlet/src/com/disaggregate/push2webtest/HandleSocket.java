/*

	Handle Socket
	  Spawn Threads that handle socket I/O
	  Provide write method to socket
	  Provide registration of object that provides write method and
	   write data to that method

	
	$Author: myudkowsky $
	$Date: 2006-06-05 19:45:04 -0500 (Mon, 05 Jun 2006) $
	$Id: HandleSocket.java 51 2006-06-06 00:45:04Z myudkowsky $
	$Revision$
	
*/

package push2web ;

import java.net.*;
import java.io.*;



public class HandleSocket {

	PrintWriter out ;
	ReadFromWeb in ;

	public void init (Socket clientSocket)
	throws IOException {
		
		// init output stream
		out = new PrintWriter(clientSocket.getOutputStream());
		
		// init input stream
		
		in = new ReadFromWeb (clientSocket) ;
		in.run() ;

	}

	public void write(String outString )
	{
		// use output method
		out.print(outString) ;

	}
	
	public void setReadHandler(PrintWriter readHandler) {
		
		in.setHandler(readHandler) ;
	}
	
}


