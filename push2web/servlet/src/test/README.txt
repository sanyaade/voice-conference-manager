Notes

	Copyright (c) 2004-2006 by M. Yudkowsky, Disaggregate (http://www.Disaggregate.com)

	$Author: myudkowsky $
	$Date: 2006-06-05 19:45:04 -0500 (Mon, 05 Jun 2006) $
	$Id: INSTALL.txt 51 2006-06-06 00:45:04Z myudkowsky $
	$Revision: 43 $


TestMessageRelay tests the ability to run a servlet; in other words, to test to see if a simple WAR file can be run under Jetty (what Voxeo uses) or under Tomcat. TestMessageRelay should be configured as a servlet, and a call to that servlet results in a call to a cgi-bin script on a different system:

wget  ==HTTP/GET==> TestMessageRelay ==HTTP/GET==> push2web/web/testCGI.py server

and the cgi-bin/testout.cgi script will print out the key-value pairs sent by TestMessageRelay.

If you can't figure this out, just ignore it. This code and example is here for historical reasons, and a running Voxeo Prophecy system doesn't really need testing  -- it'll run a servlet.

