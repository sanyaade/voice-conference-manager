/*

	Read From Web Browser
	  (button pushes, phone numbers, etc. sent from Internet)

	  Spawn Threads that handle socket I/O
	  Provide read method from socket
	  Provide registration of object that handles data from socket
	  Invoke that method when data received

	
	$Author: myudkowsky $
	$Date: 2006-07-19 10:32:06 -0500 (Wed, 19 Jul 2006) $
	$Id: ReadFromWeb.java 82 2006-07-19 15:32:06Z myudkowsky $
	$Revision: 82 $
	
*/

package push2web ;

import java.net.*;
import java.io.*;
import javax.servlet.* ;


// Thread. Reads from socket, writes to object with "write" method

class ReadFromWeb extends Thread {

	BufferedReader in = null ;
	SendToCCXML SendData = null ; 
	ServletContext sc ;
	Log l ;
	
	long sleeptime	= 200 ;	// 0.2 s timeout, until we implement blocking read()s

	public ReadFromWeb (Socket clientSocket, ServletContext srvc ) {
	
		sc = srvc ;
		l = new Log (sc) ;
	
		try {
			in = new BufferedReader( new InputStreamReader( clientSocket.getInputStream()) );
			setDataHandler (new SendToCCXML(sc) ) ;
		} catch (IOException e) {
			// l.log ("Cannot get input stream") ;
			System.exit(1);
		}
		l.log("ReadFromWeb: init success") ;
	}
	
	public void setDataHandler (SendToCCXML sd) {
	
		SendData = sd ;
	}
	
	public void run() {
	
		l.log("ReadFromWeb: running") ;
		
		String output = null ;
		
		// continuously read input
		while (true) {
		
			// first check to see if ready to read
			boolean ready = false ;
			try {
				ready = in.ready() ;
			}
			catch (IOException e) {
				// ignore
			}
			
			// begin continuous check
			// do not proceed until something available
			while (!ready) {
			
				// not ready. Emulate blocking read using sleep
				try {
					sleep(sleeptime) ;
				} catch (InterruptedException e) {
					//ignore
				}
				
				// check to see if ready
				try {
					ready = in.ready() ;
				}
				catch (IOException e) {
					// ignore
				}
				
			}
			
			// might be ready
			try {
				output = in.readLine() ;
			}
			catch (IOException e) {
				// ignore
			}

			// check if we have output
			if ( output != null ) {
				l.log("ReadFromWeb - Found string: " + output) ;
				if (SendData != null ) {
					SendData.write(output) ;
				} 
			// if no output, sleep (non-blocking read)
			}
			
			// continue through loop

		} // while()

	} // run()
}

