#! python

# Copyright (c) 2004 by M. Yudkowsky, Disaggregate (http://www.Disaggregate.com)
# See attached license for details

# $Header: /cvsroot/vcm/vcm/config/config.py,v 1.2 2005/02/16 02:04:20 myudkowsky Exp $
# $Id: config.py,v 1.2 2005/02/16 02:04:20 myudkowsky Exp $

# 1. Read in and parse the config file

# 2. Copy files to new directory, but on the way...

# 3. Look through each relevant file, finding tokens as we go
#	a. If file is not vxml, xml, cgi -- ignore the file
#	b. Otherwise look for tokens and substitute them

import sys, os, os.path, shutil, re
import xml.parsers.expat

debug = True

writeFileMode="w"	# write files, overwriting previous ones

class ConfigData:
	pass
	
ConfigData.current_element = None
ConfigData.tokens = dict()
ConfigData.collecting = None
ConfigData.source = "./"

# Files to check: CCXML, Grammar, VoiceXML, CGI, Python, HTML
ConfigData.fileextensions=['.*\.xml$','.*\.vxml$','.*\.cgi$','.*\.py$','.*\.html$']

#
# function to check for tokens while copying file
#

def checkandcopy (filename, sourcedir, destdir):

	# create the correct path to source file
	
	source = os.path.join(sourcedir,filename)
	# if debug: print sourcedir, source
	dest = os.path.join(destdir, filename)
	# if debug: print destdir, dest

	# check the filename against patterns of interesting files
	# if the file is not of the correct type, just do a regular copy
	
	doesmatch = [ re.match(pat,source) for pat in ConfigData.fileextensions ]
	
	# if debug: print 'doesmatch', doesmatch
	
	#  if full of "None":
	if doesmatch.count(None) == len(doesmatch):
		shutil.copy(source, dest)
		return
		
	# there is a match. Open file, check for tokens and do substitutions
	
	if debug: print "Modifying:", sourcedir, source
	
	srcFile=file(source)
	text=srcFile.read()
	
	for token in ConfigData.tokens.keys():
		# if debug: 'Checking', token, ConfigData.tokens[token]
		text = re.sub(token,ConfigData.tokens[token],text)
		
	# write out the string to the new file
	destFile=file(dest, writeFileMode)
	destFile.write(text)
	destFile.close()
	srcFile.close() 
	
	
	
# create parser
p = xml.parsers.expat.ParserCreate()


# handler functions for the parser

def start_element(element, attrs):


	if element == 'param':
		# current element -- needed during text collection phase
		ConfigData.current_element = attrs['name']
		
		# ConfigData gets this attribute, which might remain None if element is empty
		setattr(ConfigData, ConfigData.current_element, None)
		
		# how to handle char data
		p.CharacterDataHandler = param_char_data
		
		
	elif element == 'token':
	
		# current element -- needed during text collection phase
		ConfigData.current_element = attrs['name']
		
		# Add to config data. The item may remain None if element is empty
		ConfigData.tokens[attrs['name']] = None
		
		# how to handle char data
 		p.CharacterDataHandler = token_char_data
		
	# if debug: print ConfigData.current_element
	# if debug: print ConfigData.tokens.keys()
	
		
def end_element(name):

	# no more interesting data to collect
	p.CharacterDataHandler = char_data
	
	
def param_char_data(data):
	
	setattr(ConfigData, ConfigData.current_element, data.strip() )
	# if debug: print ConfigData.current_element, data.strip()

def token_char_data(data):
	
	ConfigData.tokens[ConfigData.current_element] = data.strip()
	# if debug: print ConfigData.current_element, data.strip()

def char_data(data):
	pass
	
p = xml.parsers.expat.ParserCreate()

p.StartElementHandler = start_element
p.EndElementHandler = end_element
p.CharacterDataHandler = char_data

fileHandle = file(sys.argv[1])

p.ParseFile(fileHandle)

# if debug: print 'token Keys:', ConfigData.tokens.keys()
# if debug: print 'Values:', ConfigData.tokens.values()
# print ConfigData.destination
# print dir(ConfigData)

# check destination for files, make certain it exists and is writeable

path_ok = os.path.isdir(ConfigData.destination) & os.access(ConfigData.destination,os.W_OK)

if not path_ok:
	print 'Directory ', ConfigData.destination, ' does not exist or is not writeable'
	sys.exit(1)

# check to make certain that source and destination are different.
# If they are not, then do not copy or modify the files


if not os.path.samefile(ConfigData.source,ConfigData.destination):

	# copy all files in source directory tree to destination directory tree
	for root, dirs, files in os.walk(ConfigData.source):
		# do not copy CVS directories
		if 'CVS' in dirs:
			dirs.remove('CVS')

		# The destination dir starts out as ConfigData.destination
		# but if we walk the source directory, we need to *add* extra stuff
		# to the destination path
	
		
		# create subdirectories as needed
		# destination dir will depend on current "root"
		
		# example:
		#  we start out in /tmp/vcm
		#  we descend to /tmp/vcm/foo/bar
		#  destdir needs to be ConfigData.destination + foo/bar
		
		destdir = os.path.join(ConfigData.destination,root.replace(ConfigData.source,""))
		
		if debug: print 'root:', root, 'destdir:', destdir
		
		newdirlist = [ os.path.join(destdir, directory) for directory in dirs ]
		[ os.mkdir(directory) for directory in newdirlist if not os.path.isdir(directory) ]
			
		# copy over all files in directory, checking each one first for tokens
		
		[ checkandcopy(filename,root,destdir) for filename in files ]
			
		
	
	