/*
	This applet sets up a com link to the host over a designated port

	Disaggregate (C) 2005, M Yudkowsky.

	$Header: /cvsroot/vcm/vcm/java/ComLink.java,v 1.1 2005/02/16 02:07:44 myudkowsky Exp $
	$Id: ComLink.java,v 1.1 2005/02/16 02:07:44 myudkowsky Exp $
*/

import java.applet.*;
import java.awt.*;
import java.awt.event.*;
import netscape.javascript.*;
import java.net.*;
import java.io.*;
import java.util.* ;

// import sun.plugin.javascript.* ;

public class ComLink extends Applet implements Runnable {

	InetAddress		address ;
	Integer			port ;
	Socket			socket ;
	BufferedWriter	outStream ;
	BufferedReader	inStream ;

	Thread			receiveThread ;
	
	JSObject		localWindow ;
	
	// init, start, stop, and run of the Applet
	
	// init() open streams

	public void init () {
	
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
		
		// window that contains the JS we want to access
		localWindow = JSObject.getWindow(this);
		
	}
	
	// start: start Threads that read/write to streams
	
	public void start () {
	
		receiveThread = new Thread(this); 
		receiveThread.start();
	}
	
	// run: Threads run, and as they run, they do stuff here.
	// This is an infinite loop...
	
	public void run() {
	
		String	incomingData ;
	
		while (Thread.currentThread() == receiveThread) {
		
			try {
				// get the data
				incomingData = inStream.readLine();
				
				// handle it
				sendDataToJS (incomingData) ;
				
			} catch (IOException e) {
				System.err.println("ComLink: Unable to read input stream");
			}
		}
	}
	
	public void stop () {
	
		// try to stop all. Ignore errors, streams might not even be open
		try {
			outStream.close() ;
		}  catch (Exception e) {}
		try {
			inStream.close() ;
		}  catch (Exception e) {}
	}
	
	
	private void sendDataToJS (String incomingData) {
	
		// JS portion must have an incoming data handler
		String[] args = { incomingData } ;
		localWindow.call("incomingJavaText", args ) ;
	}
	
	public void receiveDataFromJS (String str ) {
	
		try {
			outStream.write(str) ;	// shouldn't this be a separate thread?
			outStream.flush() ;		// don't wait for lots of stuff before sending
		} catch (IOException e) {
				System.err.println("ComLink: Unable to write/flush out stream");
		}
	}
	

}