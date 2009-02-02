/*
	Read from input stream sent by browser

	Disaggregate (C) 2006, M Yudkowsky.

	$Author: myudkowsky $
	$Date: 2006-07-06 08:25:22 -0500 (Thu, 06 Jul 2006) $
	$Revision$
	$Id: ComLinkRead.java 73 2006-07-06 13:25:22Z myudkowsky $
*/

import java.applet.*;
import netscape.javascript.*;
import java.net.*;
import java.io.*;
import java.util.* ;

public class ComLinkRead extends Thread {


	BufferedReader	inStream ;
	JSObject		localWindow ;
	Boolean			running = true ;
	
	long			sleeptime = 200 ;	// 0.2 seconds between looks
										// at least until we can find a way to block on read!!
	
	public ComLinkRead (BufferedReader in, JSObject win) {
	
		inStream = in ;
		localWindow = win ;
		this.setName("ComLinkReadThread") ;
		System.err.println("ComLinkRead: init successful");
		
	}
	
	
	// run: Threads run, and as they run, they do stuff here.
	// This is an infinite loop...
	
	public void run() {
	
		String	incomingData ;
		int		errcount = 0 ;
	
		while (running) {
		
			Boolean ready = false ; 
			
			// wait to unblock
			while ( running && !ready  ) {
				try {
					sleep (sleeptime) ;		// wait between checks (need blocking read here!)
					ready = inStream.ready() ;
				} catch (IOException e) {
					// ignore
					System.err.println("IOException while waiting for Ready") ;
				} catch (InterruptedException e) {
					// ignore
				}
			}

			// try read after unblock
			try {
				
				incomingData = inStream.readLine();
				System.err.println("ComLinkRead: received: " + incomingData) ;

				// send incoming strings to JavaScript for display
				sendDataToJS (incomingData) ;
				
			} catch (IOException e) {
				errcount += 1 ;
				if ( errcount > 16000 ) errcount = 0 ;
				System.err.println("ComLinkRead: Unable to read input stream: attempt " + Integer.toString(errcount) );
			}
		}
	}
	
	public void halt () {
	
		// stop thread
		running = false ;
	}
	
	
	private void sendDataToJS (String incomingData) {
	
		// JS portion must have an incoming data handler
		String[] args = { incomingData } ;
		localWindow.call("incomingJavaText", args ) ;
	}
}