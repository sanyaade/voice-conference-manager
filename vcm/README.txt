Notes

Copyright (c) 2004 by M. Yudkowsky, Disaggregate (http://www.Disaggregate.com)

$Header: /cvsroot/vcm/vcm/README.txt,v 1.2 2005/02/16 02:04:18 myudkowsky Exp $
$Id: README.txt,v 1.2 2005/02/16 02:04:18 myudkowsky Exp $

See license for terms of use and disclaimers!

* This project is available at Sourceforge, http://vcm.sourceforge.net. 

* This release was tested at Voxeo's servers (http://community.voxeo.com), using the Beta of their CCXML interpreter that was running in August, 2004. Subsequent releases of the Voxeo interpreter are GUARANTEED to break this code because the Beta is not fully compliant with the CCXML specification as yet -- so neither is this code.

ABOUT VOICE CONFERNCE MANAGER

Voice Conference Manager (VCM) uses VoiceXML, CCXML, and grXML to create a voice conference call system. A "clerk" calls a telephone number to set up a conference call. The clerk says the names of all the participants, and then hangs up. A server then calls each participant and adds them to the conference call.

The system also includes a web-based monitoring system. By viewing a web page, the conference owner or members of the conference call can see who is on the call. (In the future, the conference call will be controlled by the web page interface.)

ABOUT THIS RELEASE

This first release of VCM is intended for developers. It provides a good example of how to use CCXML, VoiceXML, and grXML to create a service.  It lacks certain facilities -- the voice user interface must be improved, the grXML files are not created out of a database, and similar issues remain to be resolved.

This current release includes the ability to monitor the progress of calls, in real time, via a web page.

INSTALLATION

See the file "INSTALL.txt".

