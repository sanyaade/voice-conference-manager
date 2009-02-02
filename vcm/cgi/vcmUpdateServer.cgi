#! /home/moshe/public/bin/python
# 1. Server for browser ComLink applets and send/receive from them.
# 2. Server for CGI to send us what it receives from CCXML interpreter
# 3. Send updates to CCXML interpreter (next release sometime...)
#

# Copyright (c) 2005 by M. Yudkowsky, Disaggregate (http://www.Disaggregate.com)
# See attached license for details

# $Header: /cvsroot/vcm/vcm/cgi/vcmUpdateServer.cgi,v 1.1 2005/02/16 02:18:48 myudkowsky Exp $
# $Id: vcmUpdateServer.cgi,v 1.1 2005/02/16 02:18:48 myudkowsky Exp $


import Queue, threading, cgi, pickle

import time, sys


import comlink		# communications with browser
import messages		# formatting messages

# debug

import log
debug = log.log
log.logString = "(debug)"

# turn on or off
log.logFlag = True

# Constants
# clearly, these should be passed by a Higher Authority
# or a config file
# in the meantime we will use constants

QUEUE_SIZE = 50			# total entries that can be on queue at any given time. Is *not* the size in bytes!
DEF_SOCKET_TIMEOUT = 5.0	# timeout, in seconds, for IO on sockets
RECEIVE_TIMEOUT = 5000		# timeout every 5 seconds on receive
RECV_ZERO_WAIT = 5		# wait if we receive zero length data
CYCLETIME = 5				# between pushes in test mode

CGI_BLOCK = True		# block for CGI. Otherwise we churn
CGI_TIMEOUT = 60		# at least a one-minute wait

FORMAT_BLOCK = True		# no reason to block very long 
FORMAT_TIMEOUT = 3

DEBUG_FILE = "debug.out"

# server info

HOST=''		# this host
PORT=TOKEN_BROWSER_PORT	# for use by browsers to connect to
CGI_PORT=TOKEN_CCXML_PORT	# for use by CGI programs on this server to send us what CCXML server sends them

# 
# Send data over a functioning socket
# 


# START PROGRAM

# create src and dst queues for to read/write to browsers

debug("vcmUpdateServer start...")

fromCGIqueue = Queue.Queue(QUEUE_SIZE)		# from FIFO that collects data from CCXML
toBrowserFormatQueue = Queue.Queue(QUEUE_SIZE)		# to message formatter when dest is browser
toBrowserQueue = Queue.Queue(QUEUE_SIZE)		# from message formatter to browser


# flag. Use this flag to tell threads to exit
# threads use it to tell us they have shut down
endNow = threading.Event()

# flag. If the fifo exits this flag will be set
# fifoOnExit = threading.Event()

# flag. If the message formatter exits, it sets this flag
messageOnExit = threading.Event()

# start message formatter
toBrowser = messages.ccxml2browser(toBrowserFormatQueue, toBrowserQueue, messageOnExit, endNow )
toBrowser.start()

debug ("message formatter started")

# our queue to browser (note that this should be replaced after debug with toBrowserQueue)

ourToBrowserQueue = Queue.Queue(QUEUE_SIZE)
fromBrowserQueue = Queue.Queue(QUEUE_SIZE)

# when browser connects, send this status message
greetingMsg = "You Are Connected and Ready to Receive Updates" 

# start server
host=HOST
port=PORT
server=comlink.CreateConnection(host, port, fromBrowserQueue, ourToBrowserQueue, endNow, greetingMsg )
server.start()

debug("browser comlink started")

# start looking for data from CCXML
#  data is sent from CCXML interpreter, arrives at a CGI program, which then
#  connects to this port

portCGI = CGI_PORT
reader=comlink.CreateConnection(host, portCGI, fromCGIqueue, None, endNow, None )
reader.start()		# start thread

debug ("cgi watch started")

if log.logFlag:
	f = file (DEBUG_FILE, "a+")

	
# loop and wait for incoming data
# much of the loop is debug printouts,
# with occassional checking for control-C or other requests to exit program
	
while True:
	
	current = None
	# check to see if any sub-threads exited
	if endNow.isSet():
		debug("main asked for exit")
		break
	
	# See if data received from CGI. Do not block.
	try:
		current = fromCGIqueue.get(CGI_BLOCK, CGI_TIMEOUT)
	except Queue.Empty:
		pass			# empty queue is not a problem
	except KeyboardInterrupt:		# Use control-C to end program
		print "Halt due to keyboard interrupt"
		endNow.set()
		break
	except Exception, inst:
		if log.logFlag:
			print "Type of exception is:", type(inst)
			print "Argument to exception are:", inst.args
		endNow.set()
		break

	# if we received information
	if current:
		form = pickle.loads(current)
		
		# debug: print out as a string
		if log.logFlag:
			f.write(time.asctime() + '\n' )
			for i in form.keys():
				f.write( "key: " + str(i) + " ,value " + str(form[i]) + '\n' )
			f.flush()
		
		# write data to messaging format queue
		toBrowserFormatQueue.put(form)
	
	# Check to see if formatting thread returned message
	current = None
	try:
		current = toBrowserQueue.get(FORMAT_BLOCK, FORMAT_TIMEOUT)
	except Queue.Empty:
		pass	# empty queue, return to your tasks
	except KeyboardInterrupt:		# Use control-C to end program
		print "Halt due to keyboard interrupt"
		endNow.set()
		break
	except Exception, inst:
		if log.logFlag:
			print "Type of exception is:", type(inst)
			print "Argument to exception are:", inst.args
		endNow.set()
		break
	# debug: print out as a string
	if current:
		if log.logFlag:
			f.write(time.asctime() + '\n' )
			f.write(current)
			f.flush()

	# place on queue to be sent to browser
	if current:
		ourToBrowserQueue.put(current)

# If we reach this line, program is over
