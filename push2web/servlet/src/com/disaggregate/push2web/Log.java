/*

	Log

	Instead of implementing HttpServlet in lots of threads just to log stuff,
	provide a Log() class.

	
	$Author: myudkowsky $
	$Date: 2006-07-06 15:46:10 -0500 (Thu, 06 Jul 2006) $
	$Id: Log.java 78 2006-07-06 20:46:10Z myudkowsky $
	$Revision: 78 $
	
*/

package push2web ;

// import java.net.*;
// import java.io.*;
import javax.servlet.* ;
// import javax.servlet.http.*;


public class Log {

	ServletContext sc ;		// context in which we operate

	public Log (ServletContext srvc ) {
		sc = srvc ;
		sc.log ("Log: init successfull");
	}

	public void log (String s) {
		sc.log(s);
	}
}	
