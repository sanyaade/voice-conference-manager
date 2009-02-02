#! python

# Copyright (c) 2004 by M. Yudkowsky, Disaggregate (http://www.Disaggregate.com)
# See attached license for details

# $Header: /cvsroot/vcm/vcm/cgi/checkcallerid.cgi,v 1.2 2004/09/08 00:43:35 myudkowsky Exp $
# $Id: checkcallerid.cgi,v 1.2 2004/09/08 00:43:35 myudkowsky Exp $

# This file is the base file for VCM's CGI system. It accepts an incoming CGI request and:
# 	* Validates a token if present
# 	* Packs up the data
# 	* Passes the data along to the correct script


import cgi, sys, anydbm, os

# library of string defintions
import defs


def processCGI(args):
	
	# input from field
	form=cgi.FieldStorage()
	
	# Create session ID.
	# If session ID is not valid, abort
	if form.has_key(defs.sessionid):
		try:
			sessionid = vcmdb.sessioninfo(form.getfirst(defs.sessionid)
		except:
			errorPage("Invalid session.")
	else:
		sessionid = None

	# Find function, pass data along to function
	
	if form.has_key(defs.action):
		action = form.getfirst(defs.action)
		
		# is this action supported?
		if not actionlist.has_key(action):
			errorPage("Action requested is not supported")
			
		actionlist[action](sessionid,form)
		
		

# debug tracebacks:
# import cgitb ; cgitb.enable()


# check for for proper token.

if not form.has_key('tokenid') :
	print "Did you forget your auth code?"
	sys.exit()

if form['tokenid'].value != '60e7b6a2aaa3df4086a75a63f59720f89ed77c01b03b00ed89bbfd462fa1944e6708349a2ac05d0d9676627e' \
		and form['tokenid'].value != '98' :
	print "You are an unauthorized user. Go away."
	sys.exit()


# (note: del form[] is not supported for FieldStorage)

# make copy and get rid of tokenid

params={}


#
# Open database
#


# open database for writing
# we assume it exists -- it's "impossible" to create one via CGI anyway

try:
	data=anydbm.open(database_name,write)
except anydbm.error :
	print "unable to open database... goodbye"
	sys.exit()

print "now attempting write to database"

# write data to database

for i in params.keys() :
	data[i] = params[i]

# close database

data.close()

# open database

data=anydbm.open(database_name,read)

# read out values

for i in data.keys() :
	print i, data[i]

# close

data.close()




