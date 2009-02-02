<?php

	/*

		Conference Grammar for P2006 Voice Conference Manager

	*/


	/*
		Copyright (c) 2004-2006 by M. Yudkowsky, Disaggregate http://www.Disaggregate.com
		See attached license for details and disclaimers

		$Author: myudkowsky $
		$Date: 2006-05-17 19:22:48 -0500 (Wed, 17 May 2006) $
		$Id: confgrammar.php 44 2006-05-18 00:22:48Z myudkowsky $
		$Revision: 44 $
	*/
	
	// configuration items. Includes script names, delimiters, universal timeouts
	
	require("vxml_config.inc");
	
	// PHP on P2006 requires this. Is it a configurable item?
	header('Cache-Control: no-cache');
	print ('<?xml version="1.0" encoding="UTF-8"?>') ;
	
	// our data file
	
	$data_file = $dataDir . $_REQUEST["file"] ;

?>


<grammar xmlns="http://www.w3.org/2001/06/grammar" xml:lang="en-US" root = "CONFEREES"  version="1.0" tag-format="semantics/1.0">


<rule id="CONFEREES">

	<!-- init the variable in case of prior bug -->
	<!--<tag> $ = "" ; </tag>-->

	<!-- init the properties -->
	<!--<tag> $.action = "" ; $.number = "" ; </tag>-->

	<!-- action, if any: -->
	<one-of>
		<item repeat="0-1">
			<ruleref uri="#ACTION"/>
			<tag> $.action=$ACTION.action;  $.name = $ACTION.name ; </tag>
		</item>
	</one-of>
	

	
	
	<!-- Name or phone number -->
	<one-of>
		<item repeat="0-1">
			<ruleref uri="#NAMES"/>
			<tag> $.number=$NAMES.number ; $.name = $NAMES.name ;  </tag>
		</item>
		
		

		<!--<item repeat="0-1">
			<ruleref uri="#NUMBERS"/>
			<tag> $.name=$NUMBERS.name ; $.number=$NUMBER</tag>
		</item>-->
	</one-of>
	
	
	<!--<tag> <![CDATA[ <$.action $x> <name $name> <number $number> ]]> </tag>-->
	
</rule>


<!-- $.actions: You can say "add", "finish", or nothing at all -->

<rule id="ACTION">

	<tag> $.name = "False" ; </tag>

	<one-of>
	
		<item>
			 <item repeat="0-1"> please </item> add 	<tag> <!--$="add" ;--> $.action="add";</tag>
		</item>
		
		<item>
			Finished									<tag> <!--$="Finished";--> $.action="stop";</tag>
		</item>
		
		<item>
			No more names								<tag> <!--$="No more names" ;--> $.action="stop";</tag>
		</item>
		
		<item>
			stop										<tag> <!--$="stop" ;--> $.action="stop";</tag>
		</item>
		
	</one-of>
	
</rule>

<!-- Actual names. Return number instead of name -->

<rule id="NAMES">

	<tag> $.name = "True" ; </tag>

	<one-of>

	<?php
	
	// import contact list
	
	$list = simplexml_load_file($data_file) ;
	
	// 	go thru each address, create listing
	
	foreach ($list->contact as $contact)
	{
	
		// get count of all adddress in contact
		$count = 0 ;
		foreach($contact->address as $address ) $count += 1 ;
	
		// now print out 
		foreach($contact->address as $address)
		{
		
			echo "<item>" ;
			
			if ( isset($contact["nickname"]) )
				echo $contact["nickname"] ;
			else
				echo $contact["name"] ;

			
			if ($count > 1) echo " ", $address["name"] ;
			
			echo "<tag>" ;
			$addr = trim($address) ;
			echo "$.number = \"$addr\" " ;
			echo "</tag>" ;
			
			echo "</item>\n" ;
		}
	}

	?>

	</one-of>
	
	
</rule>

<!-- speak a Number that is not on the list -->
<!-- use builtin phone grammar -->

<!--<rule id="NUMBERS">
	<one-of>
		<ruleref uri="builtin:phone"/>-->
		<!--<tag> $.name = "False" ; </tag>-->
		<!--<tag><![CDATA[ <retval $return> ]]> </tag>
	</one-of>
</rule>-->


</grammar>
