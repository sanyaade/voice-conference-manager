/*

	vcmMonitor
		* turn on the thread that monitors for incoming connections
		* display the JSP page
	
	$Author: myudkowsky $
	$Date: 2006-07-19 10:32:06 -0500 (Wed, 19 Jul 2006) $
	$Id: vcmMonitor.java 82 2006-07-19 15:32:06Z myudkowsky $
	$Revision: 82 $
	
*/

package push2web ;

import java.net.*;
import java.io.*;
import javax.servlet.*;
import javax.servlet.http.*;

public class vcmMonitor extends HttpServlet {

	public void doGet(HttpServletRequest request,
					HttpServletResponse response)
		throws IOException, ServletException {
		
		doData ( request, response ) ;
	}
	
	
	public void doPost(HttpServletRequest request,
					HttpServletResponse response)
		throws IOException, ServletException {
		
		doData ( request, response ) ;
	}
	
	public void doData (HttpServletRequest request,
					HttpServletResponse response)
		throws IOException, ServletException {
	
		log ("We have started doData") ;

		// pass request off to JSP page
		
		RequestDispatcher rd = request.getRequestDispatcher("/vcmMonitor.jsp");
		rd.forward(request, response) ;
		log ("We have started jsp page") ;
	}

}