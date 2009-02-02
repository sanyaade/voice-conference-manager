
# Copyright (c) 2004 by M. Yudkowsky, Disaggregate (http://www.Disaggregate.com)
# See attached license for details

# $Header: /cvsroot/vcm/vcm/cgi/checkcallerid.cgi,v 1.2 2004/09/08 00:43:35 myudkowsky Exp $
# $Id: checkcallerid.cgi,v 1.2 2004/09/08 00:43:35 myudkowsky Exp $

# Contains the data objects used in VCM to track calls

class Participant:
	''' data associated with participant in call '''
	
	name = None
	tel = None
	
	def __init__(self, name = None , tel = None ):
		self.name = name
		self.tel = tel

class Attendant:
	''' data associated with person authorized to make calls '''
	name = None
	tel = None
	auth = None		# authentication data

	def __init__(self, name = None , tel = None, auth = None ):
		self.name = name
		self.tel = tel
		self.auth = auth
	
	
class Call_Leg:
	''' participant and any meta data associated with each leg of call '''
	
	participant = None
	sessionID = None
	
	def __init__(self, participant = None):
		self.participant = participant

class Call:
	''' Call and meta data associated with call '''

	legs = list()		# list of legs in call
	# self.session = Session()	# has to happen at some point...
	
	def __init__(self, legs = None ):
		self.legs = legs
		
	def add_leg(self, participant):
		''' add participant to call '''
		print 'Function "add_leg" must be defined'
		
	def remove_leg(self, participant):
		''' remove leg from call '''
		print 'Function "remove_leg" must be defined'

	def _initiate(self):
		''' get CCXML instance running that can accept and add call legs '''
		print 'Function "_initiate" must be defined'
		
	def initiate(self):
		''' start calls to participants '''
		
		# get CCXML server up and running and ready to receive call legs information
		self._initiate(self)
		
		# add each call leg to the call
		for i in self.participants:
			self.add_leg(i)

	def getPhoneNumberList(self):
		
		plist = [ x.participant.tel for x in self.legs ]
		return len(plist), ' '.join(plist)
		
	