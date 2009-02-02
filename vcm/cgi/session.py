
# Copyright (c) 2004 by M. Yudkowsky, Disaggregate (http://www.Disaggregate.com)
# See attached license for details

# $Header: /cvsroot/vcm/vcm/cgi/session.py,v 1.1 2005/02/16 02:18:48 myudkowsky Exp $
# $Id: session.py,v 1.1 2005/02/16 02:18:48 myudkowsky Exp $

# Maintain a session with a CCXML server and send/receive data from it

# 
# to do list: when session is destroyed, remove it from database!
#

import random, sha		# to create random hex strings
from definitions import *
import database

class Session:
	'''	Session maintains a session with remote system & sends/receives data '''

	token = None		# session token that we use
	sessionid = None	# session token of remote
	
	stdTokens = dict()
	
	def __init__(self, authorization, starturi, resulturi):
		
		self.authorization = authorization		# CCXML authentication token
		
		# create unique string to use as token
		# in theory, we should probably check that it's not already in use
		
		random.seed()
		random.jumpahead(100)
		self.token = sha.new( str(random.random()) ).hexdigest()
		
		# URI of script that we use to start session
		self.startURI = starturi
		
		# URI of script in current use
		self.currentURI = self.startURI
		
		# Session ID of remote session
		self.sessionID = None
		
		# URI where CCXML should send responses
		self.resultURI = resulturi

	def start(self):
		''' Start session with remote URI '''
		
		# send token to remote session so we can define what session it's communicating with
		data = urllib.urlencode( { vcm_resultURI : self.resultURI , vcm_token : self.token } )
		urlHandle = urllib.urlopen(self.startURI, data)
		urlHandle.close()		# there is no actual result to read

	def sendInfo(self, signal, data):
		''' Send information -- signal and data -- to remote session '''

		# standard data sent with each call
		moredata = { vcm_resultURI : self.resultURI , vcm_token : self.token, vcm_sessionID : self.sessionID,
						vcm_eventID : signal }
				
		# add in the standard information
		data.update(moredata)
		
		data = urllib.urlencode( self.data )
		urlHandle = urllib.urlopen(self.startURI, data)
		urlHandle.close()		# there is no actual result to read

		
	def rcvInfo(self,args):
		''' Receive incoming data from remote session.
			Function is replaced by each program
		'''
		pass

def process_incoming(dbLocation,args):
	'''	Process incoming data from remote
		incoming data will contain token to differentiate it from other sessions
		look into DB, find the session, and call session-handling script
	'''
	
	# Open DB
	db = database.database(dbLocation)
	
	# decipher args into dict, find our token
	token = args[vcm_token]
	
	# Search for Session, which is inside that database
	session = db.getsession(token)
	
	# execute function that handles session data
	session.rcvInfo(args) 
	
