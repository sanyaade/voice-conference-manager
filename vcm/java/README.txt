Notes

Copyright (c) 2005 by M. Yudkowsky, Disaggregate (http://www.Disaggregate.com)

$Header: /cvsroot/vcm/vcm/java/README.txt,v 1.1 2005/02/16 02:07:44 myudkowsky Exp $
$Id: README.txt,v 1.1 2005/02/16 02:07:44 myudkowsky Exp $


To use the JavaScript and Java programs:

* Compile the Java script program. 

Note: to compile, use the javac program. I've tested this with JDK 1.5, which is the one that Sun recommends. The correct compile string is:

javac -classpath  /usr/local/share/jdk1.5.0_01/jre/lib/plugin.jar ComLink.java

on my system, at least. The "plugin.jar" contains the JSObject used in the Java source file, and unless your java compiler can find it automatically, you must tell it explicitly where to find the plugin. 

(Hint: The symptom of not finding the correct file is when the javac complains that it can't find the "netscape" objects. I had to use "jar tf name.jar" on all the jar files in my libraries and then grep until I found the file that contained the correct classes.)

* Place the ComLink.class program in the same directory as the web page itself -- unless it's signed, in which case you can put it anywhere and modify the vcmMonitor.html file to reflect the new location of the applet.

* Place the updateVcmStatus.js file into the same directory as the vcmMonitor.html file, or modify the web page to show where it is.

* Load the web page. Make a call, and you can see the web page update itself in real time.
