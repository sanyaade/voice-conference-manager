/*

	Handle Socket
	
	Methods:
	
		* Init of object
	
		* Write to socket
	
		* Register handler that reads from socket
		  handler must provide a "write" method

	
	$Author: myudkowsky $
	$Date: 2006-07-19 10:32:06 -0500 (Wed, 19 Jul 2006) $
	$Id: HandleSocket.java 82 2006-07-19 15:32:06Z myudkowsky $
	$Revision: 82 $
	
*/

package push2web ;

import java.net.* ;
import java.io.* ;
import javax.servlet.* ;


public class HandleSocket {

	PrintWriter out ;
	//ReadFromWeb in ;
	ServletContext sc ;
	
	PrintWriter[] outputList [] ;

	public HandleSocket (ServletContext current_sc, Socket s ) {
	
		sc = current_sc ;
	
		// attach listener thread to input stream
		ReadFromWeb in = new ReadFromWeb(s,sc) ;
		in.start() ; // start listener thread

		sc.log("HandleSocket: init successful") ;
	}

	public void write(String outString ) throws IOException {
	
		// Get the list of all sockets
		Socket[] socketList = (Socket[]) sc.getAttribute("currentSockets") ;
		
		// in the future, we will choose which socket to write to based on a filter
		// e.g., a login by the browser that authenticates right to receive stream
		// for the present, write to all sockets
		
		for ( int i = 0 ; i < socketList.length ; i++ ) {
		
			if (socketList[i] != null ) {
				// init output stream
				out = new PrintWriter(socketList[i].getOutputStream());
				sc.log("HS - sending to browser: " + outString);
				out.println(outString) ;	// println, not print
				out.flush() ;
			}
		}

	}
	
// 	public void setReadHandler(PrintWriter readHandler) {
// 		
// 		in.setHandler(readHandler) ;
// 	}
	
	public void writeOne ( Socket s, String outString) throws IOException {
	
		// write to a particular socket
		
		sc.log("HandleSocket.writeOne: " + outString);
	
		OutputStream os =  s.getOutputStream() ;
		byte[] outBytes = outString.getBytes() ;
		os.write(outBytes) ;
		os.flush() ;
	}

}


