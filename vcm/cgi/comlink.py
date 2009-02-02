

#
# mini server to send/receive strings
#

# Copyright (c) 2005 by M. Yudkowsky, Disaggregate (http://www.Disaggregate.com)
# See attached license for details

# $Header: /cvsroot/vcm/vcm/cgi/comlink.py,v 1.1 2005/02/16 02:18:37 myudkowsky Exp $
# $Id: comlink.py,v 1.1 2005/02/16 02:18:37 myudkowsky Exp $


import socket, threading, select, Queue, time, types

import log
debug = log.log
log.logString = "(debug)"
# turn debug on or off
log.logFlag = True

# Constants

RECV_ZERO_WAIT = 5		# time in s to wait if we receive zero-length message
RECV_BUFFER_SIZE = 8192

MAX_LISTEN = 10			# traditional number of pending requests for socket
SOCKET_TIMEOUT = 60		# socket timeout, to let us check for shutdown requests
QUEUE_TIMEOUT = 60		# timeout listening to queue, so we can check for socket shutdowns, etc.

BROWSER_DELIMITER = ";"
MESSAGE_STATUS = "Status"
MESSAGE_DATA = "Data"
CR = "\n"

class SendData(threading.Thread):
	''' Inputs: connection, data to send to connection '''
	
	def __init__(self, connection, sendq, onExit, endNow  ):
		threading.Thread.__init__(self, group=None, target=None, name=None, args=(connection, sendq, onExit, endNow), kwargs={}) 
		self.connection = connection
		self.sendq = sendq
		self.onExit = onExit		# set this flag on exit, also check to see if recv exited
		self.endNow = endNow 		# check this flag to see if time to exit
		
	def run(self):
		# look at the src. If something is in the queue, send it
		debug ("ready to send data")
		while True:
			data = None
			try:
				try:
					data = self.sendq.get(True, QUEUE_TIMEOUT)
				except Queue.Empty:
					pass
				
				# on wakeup, check to see if it's time to exit
				if self.onExit.isSet() or self.endNow.isSet():
					debug("send told to exit")
					break

				# send data
				# keep sending until all data sent
				if data:
					total = len(data)
					count = 0
					while count != total:
						count += self.connection.send(data[count:])
					# self.connection.sendall(data)
					debug ( str(count) + " bytes, beginning: " + str(data[0:32]) )
			except Exception, inst :
				self.onExit.set()		# flag that we have exited
				debug( "send thread over due to " + str(inst) )
				break
		
		try:
			self.connection.close()
		except:
			pass
		self.onExit.set()
		return

class ReceiveData (threading.Thread):
	''' Inputs: socket connection, and where to send data received over socket '''
	
	def __init__(self, connection, recvq, onExit, endNow ):
		threading.Thread.__init__(self, group=None, target=None, name=None, args=(connection, recvq, onExit, endNow), kwargs={}) 
		self.recvq = recvq
		self.onExit = onExit
		self.endNow = endNow			# if set, end
		
		# create poll to look at the local connection
		self.connection = connection
		self.pollRecv = select.POLLIN | select.POLLPRI
		self.pollAll = self.pollRecv | select.POLLERR | select.POLLHUP | select.POLLNVAL
		self.mask = ~ self.pollRecv			# mask for non-error conditions
		

	def run(self):
		'''	Look at our incoming queue, and receiv it '''
		
		self.fd = self.connection.fileno()	# file descriptor for this connection
		self.pollme = select.poll()
		# register the fd
		# self.pollme.register(self.fd, self.pollAll)
		self.pollme.register(self.fd, self.pollRecv)
		
		debug ( "ready to receive data" )
		while True:
			data = None
			try:
				if self.endNow.isSet() or self.onExit.isSet() :
					debug ( "receive thread received halt event" )
					break
				# block and wait for data or error condition
				pollOutput = self.pollme.poll(None)
				
				# reply is list of tuples with fd and reason
				for ourfd, reason in pollOutput:
				
					# if data arrives
					if reason & self.pollRecv :
						debug ("recv reason: " + str(reason)+ ", attempting to read")
						data = self.connection.recv(RECV_BUFFER_SIZE)
						if log.logFlag:
							debug ("received " + str(len(data)) + " bytes on socket" )
							if len(data):
								debug ( "string received was: " + str(data[0:32])  )
	
						# at this point we should have data
						# place it on queue
						if len(data):
							self.recvq.put(data)
						else:
							# zero-length receive means socket is irrevocably closed for reading
							debug ("received zero bytes, this socket closed")
							self.onExit.set()
							break
		
							
					# Check to see if there were other codes as well
					if reason & self.mask:		# returned because of other errors
						if reason & select.POLLHUP:
							debug ("recv halt due to hangup")
							self.onExit.set()
							break
						elif reason & select.POLLERR:
							debug ("recv halt due to error")
							self.onExit.set()
							break
						elif reason & select.POLLNVAL:
							debug ("recv halt due to invalid file descriptor")
							self.onExit.set()
							break
						else :	# ya never know...
							debug ("recv halt due to unknown code " + str(reason) )
							self.onExit.set()
							break

				if self.onExit.isSet():
					break 		# out of while loop


			except Exception, inst:
				self.onExit.set()		# send Event() that we are finished
				debug ( "receive thread over because " + str(inst) )
				break
		
		try:
			self.connection.close()		# be polite
		except:
			pass
		self.onExit.set()
		return


class CreateConnection (threading.Thread):

	def __init__(self, host, port, recvq, sendq, endNow, msg ):
		threading.Thread.__init__(self, group=None, target=None, name=None, args=(host, port, recvq, sendq, endNow, msg ), kwargs={})
		self.host = host
		self.port = port
		self.recvq = recvq
		self.sendq = sendq
		self.endNow = endNow 		# tells us to shut down
		self.msg = msg
		
	def run (self):
		try:
			mainsock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
			mainsock.settimeout(SOCKET_TIMEOUT)
			mainsock.bind((self.host, self.port))
			mainsock.listen(MAX_LISTEN)
			debug("Server listening on port " + str(self.port))
		except Exception, inst :
			debug ("Server thread over (port " + str(self.port) +") - no connection because " + str(inst) )
			self.endNow.set()		# no server means no app
			try:
				mainsock.close()	# in case we had partial open
			except:
				pass
			return
		
		# now wait for connections

		while True:
			if self.endNow.isSet():
				break
			try:
				try:
					clientsock, addr = mainsock.accept()
				except socket.timeout:		# which gives us a chance to check endNow
					continue
				debug ( 'Server (port ' + str(self.port) +')  received connection from ' + str(addr) )
	
				# we have a new socket to communicate with this client
				# prepare to spawn threads to use socket
				threadDone = threading.Event()		# used by send/recv thread pairs
				
				# send and/or receive on these sockets
				# only start if there is a queue

				if type(self.sendq) != types.NoneType:
					self.outputThread = SendData( clientsock, self.sendq, threadDone, self.endNow )
					self.outputThread.start()
					
					# is there a greeting sent to start handshaking?
					if type(self.msg) != types.NoneType:
						self.sendq.put(MESSAGE_STATUS + BROWSER_DELIMITER + self.msg + CR )

				if type(self.recvq) != types.NoneType:
					self.inputThread = ReceiveData ( clientsock, self.recvq, threadDone, self.endNow )
					self.inputThread.start()
				
			except Exception, inst :
				debug("Server thread (port " + str(self.port) +") failed because " + str(inst) )
				break
			
		
		# exiting...
		debug ( "Server thread (port " + str(self.port) +") over" )
		try:
			mainsock.close()			# try to be polite
		except:
			pass						# and ignore the fact that it probably won't close
		self.endNow.set()				# no server means no app
		return
