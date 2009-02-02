
# Copyright (c) 2004 by M. Yudkowsky, Disaggregate (http://www.Disaggregate.com)
# See attached license for details

# $Header: /cvsroot/vcm/vcm/cgi/checkcallerid.cgi,v 1.2 2004/09/08 00:43:35 myudkowsky Exp $
# $Id: checkcallerid.cgi,v 1.2 2004/09/08 00:43:35 myudkowsky Exp $

# Receive info from CCXML, which arrives as CGI information

def processCGI(args):
	'''	This funcion is a replacement for the dummy session function.
		it takes in the CGI information for this session and updates
		variables as needed, etc.
	'''
	
	# look for this particular session in the db of sessions
	
	pass