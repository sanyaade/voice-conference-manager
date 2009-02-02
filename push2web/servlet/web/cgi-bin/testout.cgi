#! /usr/bin/env python

# 	mini server to handle incoming CGI strings and parse them
# 	
# 	Copyright (c) 2005, 2006 by M. Yudkowsky, Disaggregate (http://www.Disaggregate.com)
# 	See attached license for details
# 	
# 	$Author: myudkowsky $
# 	$Date: 2006-06-05 19:45:04 -0500 (Mon, 05 Jun 2006) $
# 	$Version$


# Accept an input CGI request from the CCXML server
# get tokens, make into pickled array, and place onto fifo

quote='"'
import cgi,  sys

def acceptIncomingData():
	
	# no authentication for now
	# just return raw input 
	return cgi.FieldStorage()
	

########################
# Program Start
########################

import cgitb; cgitb.enable(False, "/var/tmp/testcgi.log")	# debug

form = acceptIncomingData()

data = dict()

for i in form.keys():
	data[i] = form.getvalue(i)

# print out proper HTTP output

print "Content-Type: text/plain"
print ""

# give a feedback event
# purely debugging at the moment
print "ReportReceived"

# print to stderr so we can debug printout
for x in data.keys():
	print >> sys.stderr, quote + x + ' = ' + data[x] + quote 

# end of script




