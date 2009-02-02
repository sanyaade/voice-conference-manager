<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<HTML>
<META http-equiv='Cache-Control' content='no-cache, must-revalidate'>
<META HTTP-EQUIV="Pragma" CONTENT="no-cache">
<META HTTP-EQUIV="Expires" CONTENT="0">

<!-- Copyright (c) 2004 by M. Yudkowsky, Disaggregate (http://www.Disaggregate.com) -->
<!-- See attached license for details -->

<!-- 
	Test JSP file
	Used to test Java class access, perhaps "forward"  by Java class 
-->

<!-- $Author: myudkowsky $ -->
<!-- $Date: 2006-06-05 19:45:04 -0500 (Mon, 05 Jun 2006) $ -->
<!-- $Header: /cvsroot/vcm/vcm/html/vcmMonitor.html,v 1.1 2005/02/16 02:04:20 myudkowsky Exp $ -->
<!-- $Id: vcmMonitor.jsp 51 2006-06-06 00:45:04Z myudkowsky $ -->

<HEAD>

<script type="text/javascript" src="updateVcmStatus.js">
</script>
<script>

	function incomingJavaText(str){
			// document.write(str) ;
			updateTable ( str ) ;
			
	}

</script>

	<TITLE>
		Conference Call Status
	</TITLE>



<APPLET CODE = "ComLink" WIDTH = "1" HEIGHT = "1" NAME = "ConnectToServer" MAYSCRIPT>
<PARAM NAME = "cache_option" VALUE="No">
<PARAM NAME = "PORT" VALUE="15334">
</APPLET>


</HEAD>


<BODY>



<p>

<table name="allCallStatus" border="1">

<tr>
	<td>Latest Message</td><td id="LatestMessage"></td>
</tr>
<tr>
	<td>Server Status</td><td id="ServerStatus">Not Connected</td>
</tr>

</table>

</p>


<p></p>


</BODY>
</HTML>
