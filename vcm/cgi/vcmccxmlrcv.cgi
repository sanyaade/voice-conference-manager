#! /home/moshe/public/bin/python

# Copyright (c) 2004 by M. Yudkowsky, Disaggregate (http://www.Disaggregate.com)
# See attached license for details

# $Header: /cvsroot/vcm/vcm/cgi/vcmccxmlrcv.cgi,v 1.1 2005/02/16 02:18:48 myudkowsky Exp $
# $Id: vcmccxmlrcv.cgi,v 1.1 2005/02/16 02:18:48 myudkowsky Exp $

# Accept an input CGI request from the CCXML server
# get tokens, make into pickled array, and place onto fifo


import cgi, pickle, sys, socket
# debug tracebacks:
# import cgitb ; cgitb.enable()# print out proper HTTP output

# Name of FIFO

HOST=''
CGI_PORT=TOKEN_CCXML_PORT		# will be replaced by config file with actual data

def acceptIncomingData():
	
	# no authentication for now
	# just return raw input 
	return cgi.FieldStorage()
	

########################
# Program Start
########################


# installation test:
# cgi.test()


form = acceptIncomingData()

data = {}

for i in form.keys():
	data[i] = form.getvalue(i)

# print "found the keys", form.keys()

# pickle the form

preserves = pickle.dumps(data)

# open socket to local service and send it the form
try:
	sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
	sock.connect((HOST, CGI_PORT))
	sock.send(preserves)
	sock.close()
except Exception, inst:
	# print "unable to write to socket because ", str(inst)
	sys.exit(1)

# print out proper HTTP output

print "Content-Type: text/plain"
print ""

# give a feedback event
# purely debugging at the moment
print "ReportReceived"

# example of data to return:
print "variables_received=" + str(len(form))

# end of script




