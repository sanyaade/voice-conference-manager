
# Copyright (c) 2004 by M. Yudkowsky, Disaggregate (http://www.Disaggregate.com)
# See attached license for details

# $Header: /cvsroot/vcm/vcm/cgi/checkcallerid.cgi,v 1.2 2004/09/08 00:43:35 myudkowsky Exp $
# $Id: checkcallerid.cgi,v 1.2 2004/09/08 00:43:35 myudkowsky Exp $

# Start a VCM session

import session
import call

#
# TEST VERSION -- HARDCODED EVERYTHING!
#

# Get list of participants we will call

names = ( ('Moshe Office', '7737648727'), ('Moshe Office', '7737648727') )

destinations = list()

[ destinations.append(call.Participant(entry[0], entry[1])) for entry in names ]


# Create current list of call legs

legs = list()

[ legs.append(call.Call_Leg(destination)) for destination in destinations ]

# create a call data structure

current_call = call.Call(legs)

print current_call.getPhoneNumberList()