/*
	This applet sets up a com link to the host over a designated port

	Disaggregate (C) 2005, M Yudkowsky.

	$Author: myudkowsky $
	$Date: 2006-07-06 08:25:22 -0500 (Thu, 06 Jul 2006) $
	$Revision$
	$Id: ComLink.java 73 2006-07-06 13:25:22Z myudkowsky $
*/

import java.applet.*;
import netscape.javascript.*;
import java.net.*;
import java.io.*;
import java.util.* ;

public class ComLink extends Applet {

	BufferedWriter	outStream ;
	BufferedReader	inStream ;

	ComLinkRead		inStreamHandler ;
	ComLinkWrite	outStreamHandler ;

	JSObject		localWindow ;
	
	// called when applet goes live
	public void init () {
	
	
		InetAddress		address =	null ;
		Integer			port =		null ;
		Socket			socket =	null ;

		// Name of host to connect to
		// Wonder if this will work if no hostname? It must...
		String host = getCodeBase().getHost();
		
		// In this version, PORT is a <PARAM>
		// There's always the possibility that it's send by JS to Java
		port = Integer.parseInt(getParameter("PORT")) ;
		
		
		// Get address of host
		try {
			address = InetAddress.getByName(host);
        } catch (UnknownHostException e) {
			System.err.println("ComLink: Couldn't get Internet address: Unknown host");
        }

		// open TCP socket to host
		try {
			socket = new Socket(address, port);
		} catch (IOException e) {
			System.err.println("ComLink: Couldn't create new DatagramSocket");
			return;
		}

		
		// open input and output streams
		
		try {
			outStream = new BufferedWriter( new OutputStreamWriter(socket.getOutputStream()));
			inStream = new BufferedReader( new InputStreamReader(socket.getInputStream()));
		} catch (IOException e) {
			System.err.println("ComLink: Unable to create outStream or Instream");
		}
		
		// find the JS Object of browser window
		// allows us to write to that window's JS
		localWindow = JSObject.getWindow(this);

		// create objects to handle streams
		
		inStreamHandler = new ComLinkRead(inStream, localWindow) ;
		inStreamHandler.start() ;	// thread that monitors input at all times
		
		outStreamHandler = new ComLinkWrite(outStream, localWindow) ;
		
		System.err.println("ComLink: init successful") ;
		
		// == DEBUG ==
/*		if (outStreamHandler == null ) {
			System.err.println("Writehandler really is null!") ;
		} else {
			System.err.println("Write handler seems to be fine.") ;
		}
		if (inStreamHandler == null ) {
			System.err.println("Readhandler really is null!") ;
		} else {
			System.err.println("Read handler seems to be fine.") ;
		}*/
		// == DEBUG ==
		
		
	}
	
	// == DEBUG ==
	// displayed each time window is uncovered
/*	public void start() {
	
		if (outStreamHandler == null ) {
			System.err.println("2-Writehandler really is null!") ;
		} else {
			System.err.println("2-Write handler seems to be fine.") ;
		}
		if (inStreamHandler == null ) {
			System.err.println("2-Readhandler really is null!") ;
		} else {
			System.err.println("2-Read handler seems to be fine.") ;
		}
	}*/
	// == DEBUG ==
	
	// close streams on applet exit
	public void destroy () {
	
		// try to stop all. Ignore errors, streams might not even be open
		try {
			outStream.close() ;
		}  catch (Exception e) {}
		try {
			inStream.close() ;
			inStreamHandler.halt() ;
			System.err.println("ComLink: closed inStreamHandler") ;
		}  catch (Exception e) {}
	}
	
	
	public void receiveDataFromJS (String str ) {
	
		try {
			outStreamHandler.write(str) ;
		} catch (Exception e) {
			System.err.println("ComLink: Exception at receiveDataFromJS") ;
			System.err.println("ComLink: Exception details: " + e.toString() ) ;
			e.printStackTrace() ;
		}
	}
	

}