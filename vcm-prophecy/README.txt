Notes

	Copyright (c) 2004-2006 by M. Yudkowsky, Disaggregate (http://www.Disaggregate.com)

	$Author: myudkowsky $
	$Date: 2006-05-17 19:20:45 -0500 (Wed, 17 May 2006) $
	$Id: README.txt 43 2006-05-18 00:20:45Z myudkowsky $
	$Revision: 43 $

See license for terms of use and disclaimers!

* This project is available as a branch of the Voice Conference Manager, http://vcm.sourceforge.net.  See the INSTALL.txt file for details.

* This project is tailored for the Prophecy 2006 VoiceXML/CCXML/SRGS/SIP plaform. See Voxeo.com and http://www.Prophecy2006.com for further information about the Prophecy 2006 platform.

* This project uses Voxeo's CCXML version 1.0 interpreter, its VoiceXML 2.0 interpreter, and the SIP, ASR, and TTS available as part of Prophecy 2006.

* Bug reports should be filed at http://vcm.sourceforge.net. 

ABOUT VOICE CONFERNCE MANAGER

Voice Conference Manager (VCM) uses VoiceXML, CCXML, and SRGS (formerly grXML) to create a voice conference call system. A "clerk" or "attendant" calls a telephone number to set up a conference call. The clerk says the names of all the participants, and then hangs up. A server then calls each participant and adds them to the conference call.

The system also includes a web-based monitoring system. By viewing a web page, the conference owner or members of the conference call can see who is on the call. (In the future, the conference call will be controlled by the web page interface.)

ABOUT THIS RELEASE

This  release of VCM is intended for developers on the Prophecy 2006 platform. It provides a good example of how to use CCXML, VoiceXML, and SRGS to create a service. The announcements in the VoiceXML portion of the call are, to put it mildly, skewed towards the needs of a developer, not an actual service.

This current release does not include the ability to moinitor, in real time, the progress of the conference call via a web page. I will try to port that service and integrate it into the Prophecy 2006 platform.

INSTALLATION

See the file "INSTALL.txt".

TECHNOLOGY

This is an introduction to how the calls work. The VCM web site at http://vcm.sourceforge.net has an overview of how the main branch works; this document discusses how the P2006 version works.

The P2006 version of VCM is intended to work within the license limitations of P2006. In particular, only two CCXML scripts can run at a single time. Instead of running several different scripts simultaneously, this P2006 version creates a "chain" of scripts, one for each call, which allows us to use the maximum number of instances but still use one CCXML script per call and avoid confusion.

THE CLERK

A caller to the system -- the "clerk" -- reaches the script ccxml/conference.php. Each clerk is identified via caller id, via a script in cgi/checkcallerid.php. If the clerk is a legitimate user, the checkcallerid script passes information about the clerk to back to conference.php.

Conference.php then calls vxml/conference.php. Vxml/conference.php calls vxml/confgrammar.php, giving it the name of the XML file that contains the contact list for that clerk. Confgrammar.php generates a grammar, and vxml/conference.php collects a list of names from the clerk.

After the dialog ends, control passes back to ccxml/conference.php. Conference.php calls another short VoiceXML file to read off a "please hang up now" announcement and hangs up on the clerk. The script fetches the next script, ccxml/conf_init.php, and then does a goto to that script.

THE CONFERENCE OBJECT

Conf_init.php creates a conference object, and then sends the ID of that conference object along with a list of names to the next script, conf_legs.php. Once the first conf_legs.php script is created, conf_init.php sends a delayed "start" signal to conf_legs.php, and then exits. The goal of the delayed start signal is to avoid the problem of having too many scripts running at once.

THE CALL LEGS

The first copy of conf_legs.php uses PHP to find the first number in the list of people to call (that we obtained ultimately from conference.php). If there are any remaining numbers to call, the current  copy of conf_legs.php will create another copy of conf_legs.php and send that new copy the list of remaining calls. In this way, each outbound call has its own copy of conf_legs.php, with one CCXML script per leg. In other words, it's a chain.

Each copy of conf_legs.php has a pointer to the copy of conf_legs.php that precedes it and follows it in the chain. If something goes wrong with the call, a "teardown" signal propogates through the chain (in theory) to inform all call legs to terminate. This capability is not thoroughly tested at this time.

Each instance of conf_legs.php makes an outgoing call, announces to the callee that a conference call is in progress, and adds that callee to the call.

At hangup time, there's a bit of a problem, which at the present is solved with a kludge. The conference object is supposed to maintain a list of all connections to it, and the last instance of conf_legs.php to exit would in theory destroy the conference object. The current build of P2006 does not support the conference object. Instead, the last instance of conf_legs.php in the chain destroys the conference object -- even though other instances may be using the conference object, the object is destroyed and they would be cut off. This kludge will be fixed in future releases.

All instances of conf_legs.php terminate when the caller on that instance hangs up, or if a "teardown" is released and the instance hangs up on the caller.

