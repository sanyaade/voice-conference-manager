/*
	Read from input stream sent by browser

	Disaggregate (C) 2006, M Yudkowsky.

	$Author: myudkowsky $
	$Date: 2006-07-06 08:25:22 -0500 (Thu, 06 Jul 2006) $
	$Revision$
	$Id: ComLinkWrite.java 73 2006-07-06 13:25:22Z myudkowsky $
*/

import java.net.*;
import java.io.*;
import java.util.* ;
import java.applet.*;
import netscape.javascript.*;

public class ComLinkWrite {


	BufferedWriter	outStream ;
	JSObject		localWindow ;
	
	public ComLinkWrite (BufferedWriter out, JSObject win) {
	
		outStream = out ;
		localWindow = win ;
		System.err.println("ComLinkWrite: init successful");
	}
	
	public void write (String str ) {
	
		
		try {
			outStream.write(str) ;	// should this be a separate thread?
			outStream.newLine() ;	// far is is doing a readline()
			outStream.flush() ;		// don't wait for lots of stuff before sending
			// DEBUG
			System.err.println("ComLinkWrite sent: " + str ) ;

		} catch (Exception e) {
			System.err.println("ComLinkWrite: Exception at receiveDataFromJS") ;
			System.err.println("ComLinkWrite: Exception details: " + e.toString() ) ;
			e.printStackTrace() ;
		}
	}

}