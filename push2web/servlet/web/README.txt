Notes

	Copyright (c) 2004-2006 by M. Yudkowsky, Disaggregate (http://www.Disaggregate.com)

	$Author: myudkowsky $
	$Date: 2006-06-05 19:45:04 -0500 (Mon, 05 Jun 2006) $
	$Id: INSTALL.txt 51 2006-06-06 00:45:04Z myudkowsky $
	$Revision: 43 $
	
This directory contains a python script that acts as a web server, along with a cgi-bin subdirectory. The goal of these two files is to act as a test recipient for the TestMessageRelay class. A message sent to TestMessageRelay is relayed to this script.

To use, start this python script in this directory. Any HTTP GET messages to the port configured in the script (15334) will be handed off to the cgi-bin script, which will print out the key/value pairs on stderr.