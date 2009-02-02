#! python

# Copyright (c) 2005 by M. Yudkowsky, Disaggregate (http://www.Disaggregate.com)
# See attached license for details

# $Header: /cvsroot/vcm/vcm/cgi/checkcallerid.cgi,v 1.2 2004/09/08 00:43:35 myudkowsky Exp $
# $Id: checkcallerid.cgi,v 1.2 2004/09/08 00:43:35 myudkowsky Exp $

import urllib


############
# Constants
############


debug = True


# send correct token to this URL and a designated CCXML script will start
triggerURL = "http://session.voxeo.net/CCXML.start"

# CCXML Test
param_list={'tokenid':'TOKEN_CGI_RUN_CCXML_TESTPARM'}

################
# Program Start
################

custom = param_list.copy()
customCoded=urllib.urlencode(custom)

result=urllib.urlopen(triggerURL, customCoded)

if debug:
	 actual = result.geturl()
	 print actual