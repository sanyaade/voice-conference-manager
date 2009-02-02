Notes

Copyright (c) 2004 by M. Yudkowsky, Disaggregate (http://www.Disaggregate.com)

$Header: /cvsroot/vcm/vcm/config/README.txt,v 1.2 2005/02/16 02:04:20 myudkowsky Exp $
$Id: README.txt,v 1.2 2005/02/16 02:04:20 myudkowsky Exp $

See license for terms of use and disclaimers!

How to configure the system.

PART 1:

The script "config.py" is a Python script that reads the file "config.xml" to configure all the variables in the VCM project. Config.py accomplishes the following actions:

* It copies the entire directory structure of the VCM project to a designated location.
* The "config.dat" file is read.
* Any configuration variable -- which are given as tokens -- in any VCM script is replaced with a value from config.dat. Only *.vxml, *.cgi, and *.xml scripts are have token replacement -- *.txt files, such as this one, do not have replacement.

Usage: python config.py config_data_file

It will copy all subdirectories. Example: In my current system, I have a vcm directory with subdirectories for ccxml, vxml, etc. First, I make a copy of the file "config.xml", modify it, and place it my tmp directory. In the copy of the config.xml directory, I set all the variables, and in particular I give it a new location for the output files. Then, from the vcm directory I run:

python config/config.py ~/tmp/config.xml

and it copies all files and subdirectories of the vcm project to the location I designate, e.g., ~/tmp/vcmtest.

NOTE! NOTE! Since the config.xml file is an xml file, it too will be copied over and modified! Be careful, since the modified copy of the config isn't useful.

The file "config.xml" contains comments that explains each variable. Right now there's only a handful.

PART 2:

The file "convert" is a shell script that will convert our CCXML files, which are written to the CCXML "Last Call" specification (pre-1.0 for public comment), into Voxeo's current format. Run "convert *.xml" to convert all CCXML files in that directory.

