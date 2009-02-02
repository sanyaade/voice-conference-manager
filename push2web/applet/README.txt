Notes

Copyright (c) 2005 by M. Yudkowsky, Disaggregate (http://www.Disaggregate.com)

	$Author: myudkowsky $
	$Date: 2006-08-24 08:57:54 -0500 (Thu, 24 Aug 2006) $
	$Revision: 104 $
	$Id: README.txt 104 2006-08-24 13:57:54Z myudkowsky $


The easiest way to install the monitoring web page is to put the contents of the applet directory into a subdirectory of Voxoe/www, for example, Voxeo/www/vcm. To see the web page, on that server go to the following URL:

http://127.0.0.1:9990/vcm/vcmMonitor.html

After the page fully loads, you should see the message "Status;Connected" in the "Latest Message" window.

Technically, the Java files (*.java) aren't actually needed there, and neither is this README file. The js, html, and class files are what's truly needed.


If you modify the Java files, then you will have to recompile them. Instructions follow.

* Compile the Java script program. 

Note: to compile, use the javac program. I've tested this with JDK 1.5, which is the one that Sun recommends. The correct compile string is:

javac -sourcepath . -classpath  /usr/local/share/jdk1.5.0_06/jre/lib/plugin.jar ComLink.java

on my system, at least. (The "plugin.jar" contains the JSObject used in the Java source file, and unless your java compiler can find it automatically, you must tell it explicitly where to find the plugin.) Note that I've had trouble using the "Kaffe" alternative Javac compiler with the JDK 1.5.0_06 file. 

(Hint: The symptom of not finding the correct file is when the javac complains that it can't find the "netscape" objects. I had to use "jar tf name.jar" on all the jar files in my libraries and then grep until I found the file that contained the correct classes.)

* Place the three class files, ComLink.class, ComLinkRead.class, and ComLinkWrite.class in the same directory as the web page itself -- unless it's signed, in which case you can put it anywhere and modify the vcmMonitor.html file to reflect the new location of the applet.

* Place the updateVcmStatus.js file into the same directory as the vcmMonitor.html file, or modify the web page to show where it is.

* Load the web page. Make a call, and you can see the web page update itself in real time.
