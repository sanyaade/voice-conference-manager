Notes

Copyright (c) 2005 by M. Yudkowsky, Disaggregate (http://www.Disaggregate.com)

$Header: /cvsroot/vcm/vcm/html/README.txt,v 1.1 2005/02/16 02:04:20 myudkowsky Exp $
$Id: README.txt,v 1.1 2005/02/16 02:04:20 myudkowsky Exp $


The localTest.html file can be used to test the JavaScript component without a server; it uses a timeout and then calls the JS function -- without using the Java to connect to a sever. Very useful to check the JS without using the server and CCXML server.

vcmMonitor.html monitors the calls in progress. All of them at once; each new conference call creates a new table. There's no security, and if you open multiple instances of this monitoring page, each page may get a different part of the server's CCXML udpates at random. In other words, I'm showing the basic connectivity, and linking each user their own conference comes in the next revision.