#
# Take messages from queues, format them, place onto outgoing queues.
#

# Copyright (c) 2005 by M. Yudkowsky, Disaggregate (http://www.Disaggregate.com)
# See attached license for details

# $Header: /cvsroot/vcm/vcm/cgi/checkcallerid.cgi,v 1.2 2004/09/08 00:43:35 myudkowsky Exp $
# $Id: checkcallerid.cgi,v 1.2 2004/09/08 00:43:35 myudkowsky Exp $


import os, threading, select, sys, time, Queue

import log
debug = log.log
log.logString = "(debug)"
# turn debug on or off
log.logFlag = False


FIFOBLOCK = True	# block for fifo
FIFOTIMEOUT = 5		# 5s wait

# List of items sent by CCXML:
CCXML_CALLID = "confName"
CCXML_NAME = "destName"
CCXML_PHONENUMBER = "destPhone"
CCXML_REPORT = "report"

FORMAT_OPEN = "("
FORMAT_CLOSE = ")"
FORMAT_DASH = "-"
FORMAT_SPACE = " "

VALUE_NOT_PRESENT = "CCXML Error, Value Not Sent"

# delim to use when sending to browser:
BROWSER_DELIMITER = ";"
MESSAGE_STATUS = "Status"
MESSAGE_DATA = "Data"
CR = '\n'				# Java expects a "CR" at the end of the string

class ccxml2browser (threading.Thread):

	'''	get data from queue
		format for browser
		place on queue for transmittal to browser '''
	
	def __init__(self, inq, outq, onExit, termFlag ):
		threading.Thread.__init__(self, group=None, target=None, name=None, args=(inq, outq, onExit, termFlag), kwargs={}) 
		self.inq = inq
		self.outq = outq
		self.onExit = onExit			# if we exit
		self.termFlag = termFlag		# set  by someone else if we are being told to exit
		
	def _format(self, data):
		'''	Format data into somethign the browser can understand '''
		
		# check for each required key and place into list.
		# if key is not present, use VALUE_NOT_PRESENT as the return value
		# use "getfirst" so that we are robust in face of accidental multiple values
		try:
			newdata = [ data.get(x,VALUE_NOT_PRESENT) for x in (CCXML_CALLID,CCXML_NAME,CCXML_PHONENUMBER,CCXML_REPORT) ]
		except Exception, inst:
			debug ("_format exception is:" + str(type(inst)) + " Argument to exception are: " + str(inst.args) )
			raise Exception, inst

		# reformat phone number if it is 10 digits
		if len(newdata[2]) == 10 :
			newdata[2] = FORMAT_OPEN + newdata[2][0:3] \
				+ FORMAT_CLOSE + FORMAT_SPACE \
				+ newdata[2][3:6] + FORMAT_DASH \
				+ newdata[2][6:10]
		
		return BROWSER_DELIMITER.join([MESSAGE_DATA]+newdata) + CR
		
	
	def run(self):
	
		# wait on queue. Timeout once in a while so that
		# we can exit the thread if the program terminates 

		debug ( "ready to receive data" )
		
		while True:
			data = None
			try:
				if self.termFlag.isSet():
					debug ( "message format thread halting by request" )
					break
				# wait for data on Queue
				try:
					data = self.inq.get(FIFOBLOCK, FIFOTIMEOUT)
				except Queue.Empty:
					continue
				except Exception, inst:
					debug ("Type of exception is:" + str(type(inst)) + " Argument to exception are: " + str(inst.args) )
					onExit.set()
					break
				
				# we have data. Format it.
				debug ("Data into ccxml2browser: " + str(data) )
				data = self._format(data)
				debug ("Data sent by ccxml2browser: " + str(data) )
				
				# place data onto output queue
				self.outq.put(data)

			# thread must terminate because of unknown error
			except Exception, inst:
				self.onExit.set()		# send Event() that we are finished
				self.termFlag.set()		# no more message formatter, might as well shut down
				debug ( "Message format thread over because " + str(inst) )
				break
		
		self.onExit.set()
		debug("message thread halted")
		return
