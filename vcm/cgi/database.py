
# Copyright (c) 2004 by M. Yudkowsky, Disaggregate (http://www.Disaggregate.com)
# See attached license for details

# $Header: /cvsroot/vcm/vcm/cgi/checkcallerid.cgi,v 1.2 2004/09/08 00:43:35 myudkowsky Exp $
# $Id: checkcallerid.cgi,v 1.2 2004/09/08 00:43:35 myudkowsky Exp $

# Get data into and out of database
# we could do this directly, but this method lets us change databases at some point

import anydbm
import cPickle

class database:

	def __init__(self, name,mode="r"):
		self.db = anydbm.open(name,mode)

	def getsession(self, token):
		'''	Get session with particular token from database '''
		
		# Token not in db, return None
		if not self.db.has_key(token):
			return None
			
		return cPickle.loads(self.db[token])
		
	def putsession(self, session):
		'''	Place session object into database '''
		
		self.db[session.token] = cPickle.dumps(session)
		
	def listsessions(self):
		'''	List tokens of all sessions in database '''
		
		return self.db.keys()
