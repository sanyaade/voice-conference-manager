<?php

	/*
		Entry point for P2006 Voice Conference Manager
		
		User actions:
			Call in
			State names of people to call, up to limit
			Hang up
			
		P2006 actions:
		
			Get names of people
			Pass information on to next script
		
		No reporting of status to external servers. We may in the future report
		status from this server and make that information available.

	*/


	/*
		Copyright (c) 2004-2006 by M. Yudkowsky, Disaggregate http://www.Disaggregate.com
		See attached license for details and disclaimers

		$Author: myudkowsky $
		$Date: 2006-05-15 12:52:16 -0500 (Mon, 15 May 2006) $
		$Id: conference.php 39 2006-05-15 17:52:16Z myudkowsky $
		$Revision: 39 $
	*/
	
	// configuration items. Includes script names, delimiters, universal timeouts
	
	require("vxml_config.inc");
	
	// PHP on P2006 requires this. Is it a configurable item?
	header('Cache-Control: no-cache');
	print ('<?xml version="1.0" encoding="UTF-8"?>') ;

?>
<vxml version="2.0">

<!-- Values out -->
<var name="count" expr="0"/>
<var name="numberlist" expr="new String()"/>
<var name="labellist" expr="new String()"/>
<var name="delimiter" expr="'<?php echo($delimiter)?>'"/>
<var name="maxCalls" expr="<?php echo($_REQUEST["maxCalls"])?>"/>

<!-- Internal values and flags -->
<var name="phone_number_attempts" expr="0"/>

<!-- Grammar of names and numbers -->


<?php
	// define location of grammar
	
	// requested
	$request = $_REQUEST["grammar_menu"] ;
	
	$grammar = $grammar_location . "?" . "file=" . $request ;

?>


<form id="get_phone_number">
	
	<field name="phone_number">
		
		<grammar src="<?php echo($grammar)?>" type="application/srgs+xml"/>

		<prompt cond="phone_number_attempts == 0">
			Say a person's name to add them to the call.
			You can also say phone numbers.
			When finished, just say "finished."
		</prompt>

		
		<prompt cond="phone_number_attempts > 0" >
			beep
		</prompt>
		
		
		<filled>
			
			<assign name="phone_number_attempts" expr="phone_number_attempts + 1"/>
			
			<log expr="'***LOG*** Action: ' 		+ phone_number$.interpretation.action"/>
			<log expr="'***LOG*** Name: '			+ phone_number$.interpretation.name"/>
			<log expr="'***LOG*** Number: ' 		+ phone_number$.interpretation.number"/>
			<log expr="'***LOG*** Utterance: '		+ phone_number$.utterance"/>
			
			<!-- Determine if we are finished or not -->
			<if cond="phone_number$.interpretation.action == 'stop'">
				<log expr="'***LOG*** sending ' + numberlist + ' ' + labellist + ' ' + count" />
				<exit namelist="numberlist labellist count"/>
			</if>
			
			<!-- Not finished. Add current number to list -->
			<assign name="count" expr="count + 1"/>
			
			<if cond="count &gt; 1">
				<!-- add space before adding string -->
				<assign name="numberlist" expr="numberlist += delimiter"/>
				<assign name="labellist" expr="labellist += delimiter"/>
			</if>
			
			<if cond="phone_number$.interpretation.name == 'True'">
				<assign name="labellist" expr="labellist += phone_number$.utterance"/>
			<else/>
				<assign name="labellist" expr="labellist += 'User Defined'"/>
			</if>
			
			<assign name="numberlist" expr="numberlist += phone_number$.interpretation.number"/>
			
			<if cond="count &gt;= maxCalls">
				<!-- max allowed. finish and exit -->
				<prompt>
					You have added the maximum number of people to the list.
				</prompt>
				<log expr="'***LOG*** sending ' + numberlist + ' ' + labellist + ' ' + count" />
				<exit namelist="numberlist labellist count"/>
			<else/>
				<!-- continue adding names -->
				<clear/>
			</if>

		
		</filled>
		
		<!-- the following horrible announcements are placeholders. Someday we will have a real voice UI -->
		
		<nomatch>
			<prompt> Did not match </prompt>
		</nomatch>
		<noinput>
			<prompt> Did not hear </prompt>
		</noinput>
		
	
	</field>

	
</form>


<!-- find choice by catching event -->

<catch event="choice">
	<var name="menu_choice" expr="_message"/>
	<exit namelist="menu_choice"/>
</catch>

</vxml>