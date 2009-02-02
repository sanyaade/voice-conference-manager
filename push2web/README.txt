Notes

	Copyright (c) 2004-2006 by M. Yudkowsky, Disaggregate (http://www.Disaggregate.com)

	$Author: myudkowsky $
	$Date: 2006-07-28 10:48:44 -0500 (Fri, 28 Jul 2006) $
	$Id: README.txt 92 2006-07-28 15:48:44Z myudkowsky $
	$Revision: 43 $

See license for terms of use and disclaimers!

* This project is available as a branch of the Voice Conference Manager, http://vcm.sourceforge.net.  See the INSTALL.txt file for details.

* This project is tailored for the Prophecy 2006 VoiceXML/CCXML/SRGS/SIP plaform. See Voxeo.com and http://www.Prophecy2006.com for further information about the Prophecy 2006 platform.

* This project uses Voxeo's CCXML version 1.0 interpreter, its VoiceXML 2.0 interpreter, and the SIP, ASR, and TTS available as part of Prophecy 2006.

* Bug reports should be filed at http://vcm.sourceforge.net. 

WHAT IT DOES

Push2web exists to provide VCM with a web page that can monitor and control a conference call. When installed, it creates a web page, typically at http://127.0.0.1:9990/vcm/vcmMonitor.html, that monitors the progress of each and provides control buttons that can mute or drop a participant.

Using the push2web package is very simple. It's a drag-and-drop installation.

BASICS

Push2web is a Java servlet that pushes information to a web browser. Unlike AJAX -- it was written just before AJAX became popular -- it doesn't rely on the browser to request information or refersh. Instead, the server can push information, such as updates, to the browser.


HOW IT WORKS

Push2web comes in two parts. First, there's a browser-side part. The browser uses Java to open a socket to the server. Any information it receives over this socket is passed to JavaScript, and the JavaScript manipulates the web page (using the DOM model, just as is done in AJAX). These files are found in the "applet" directory.

On the web server side, there's a servlet. The servlet contains several Java classes. One class creates a Listener that starts up as soon as the servlet is loaded by the server, and spawns a thread that listens for connections. Other Java classes accept information to send to the browser, format it, and send it along.

To send information to the browser, I call a web page ("http://localhost:port/push2web/send.do") with the appropriate information in a POST or GET, and that info is re-formatted and sent to the browser.

WHY PUSH2WEB EXISTS

I use push2web for a very specific purpose: A web page that monitors the progress of a conference call. Each time the state of the conference call changes, the (CCXML) script that runs the conference call does a POST to the appropriate URL. The update information in that post goes to the Web page, which then contains the correct inforamtion. This is true server push. It's an interesting part of Voice Conference Manager.

ABOUT VOICE CONFERNCE MANAGER

Voice Conference Manager (VCM) uses VoiceXML, CCXML, and SRGS (formerly grXML) to create a voice conference call system. A "clerk" or "attendant" calls a telephone number to set up a conference call. The clerk says the names of all the participants, and then hangs up. A server then calls each participant and adds them to the conference call.

The system also includes a web-based monitoring system. By viewing a web page, the conference owner or members of the conference call can see who is on the call. (In the future, the conference call will be controlled by the web page interface.)

ABOUT THIS RELEASE 

This is the very first release. I'll be astonished if there are no bugs.

INSTALLATION

See the file "INSTALL.txt".

DOCUMENTATION

Documentation, if any, is in various text files or at http://vcm.sourceforge.net.



