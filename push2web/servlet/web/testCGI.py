# 	Receive HTTP on defined socket
# 		serve cgi-bin script
#		(and that script prints key-value pairs
#		Goal: provide printout of messages to port (15334)
#		 which tests TestMessageRelay class


# 	Copyright (c) 2006 by M. Yudkowsky, Disaggregate (http://www.Disaggregate.com)
# 
# 	$Author: myudkowsky $
# 	$Date: 2006-06-07 09:25:34 -0500 (Wed, 07 Jun 2006) $
# 	$Id: testCGI.py 54 2006-06-07 14:25:34Z myudkowsky $
# 	$Revision: 43 $

import log
log.logFlag=True
debug=log.log

import CGIHTTPServer, BaseHTTPServer
import Queue, threading


very_long_timeout = 1000


# override function for our server to avoid log functions


class ourCGIHandler(CGIHTTPServer.CGIHTTPRequestHandler):
	# override annoying log messages to stderr!
	def log_message(self, format, *args):
		if log.logFlag:
			CGIHTTPServer.CGIHTTPRequestHandler.log_message(self,format,*args)
	

def cgiserve( host, port , HandlerClass = ourCGIHandler, ServerClass = BaseHTTPServer.HTTPServer):
	
	# change dir to the dir of this module
	# chdir(path)
	
	ouraddress = ( host, port )
	httpd = ServerClass(ouraddress, HandlerClass)

	h, p = httpd.socket.getsockname()
	debug ( "Serving HTTP for " + str(h) +  " port " + str(p) )
	httpd.socket.settimeout(very_long_timeout)
	
	while True:
		debug ("pending to handle single request")
		httpd.handle_request()


class runCGIServer(threading.Thread):
	
	def __init__(self, host, port  ):
		threading.Thread.__init__(self, group=None, target=None, name=None, \
			args=(host, port ),\
			kwargs={}) 
		self.host = host
		self.port = port
		
	def run(self):
		try:
			cgiserve(self.host, self.port)
		except Exception, inst :
			debug( "EXCEPTION: send thread over due to '" + str(inst) + "'" )
			return
		
		debug("CGI server thread is over")
		
		return


def startServer( host, port):
	

	x = runCGIServer ( host, port )
	x.start()
	debug("CGI server started")


# if this is standalone script:

if __name__ == "__main__" :
	
	startServer ( "bagpipes", 15334 )
