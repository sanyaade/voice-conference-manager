<?php

	/*
		Start conference call.

		Use VoiceXML dialog to find names to contact.
		
		Create conference object.
		
		Start call 1st call leg in chain of call legs and exit.
		
	*/


	/*
		Copyright (c) 2004-2006 by M. Yudkowsky, Disaggregate http://www.Disaggregate.com
		See attached license for details and disclaimers

		$Author: myudkowsky $
		$Date: 2006-07-19 10:29:42 -0500 (Wed, 19 Jul 2006) $
		$Id: conf_init.php 81 2006-07-19 15:29:42Z myudkowsky $
		$Revision: 81 $
	*/
	
	// configuration items. Includes script names, delimiters, universal timeouts
	
	require("ccxml_config.inc");
	
	// PHP on P2006 requires this. Is it a configurable item?
	header('Cache-Control: no-cache');
	print ('<?xml version="1.0" encoding="UTF-8"?>') ;

?>
<ccxml version="1.0">

<!-- list of symbolic substate names: -->

<var name="ci_init"					expr="'ci_init'"/>
<var name="ci_makeConfObject"		expr="'ci_makeConfObject'"/>
<var name="ci_confInProgress"		expr="'ci_confInProgress'"/>
<var name="ci_Done"					expr="'ci_Done'"/>
<var name="ci_exit"					expr="'ci_exit'"/>

<!-- user event names -->
<var name="nextstate"				expr="'nextstate'"/>


<!-- Vars used throughout -->

	<!-- overall state name. Initialize: -->
	<var name="ci_callState" expr="ci_init"/>
	
	<!-- name of conference object -->
	<var name="confID" expr="''"/>
	
	<!-- ID of this session -->
	<var name="home" expr="session.id"/>
	

<!-- Variables used in various substates -->
	<!-- ci_welcomeBase -->
		<var name="checkCallerID_timeout_count"		expr="0"/>
		<var name="checkCallerID_timeout_max"		expr="1"/>
		<var name="ci_welcomeBase_gotdata"			expr="0"/>
		<var name="ci_welcomeBase_finishedgreeting"	expr="0"/>
	
	<!-- ci_findUserRequest -->
		<var name="ci_findUserRequest_count"			expr="0"/>
		

	<!-- ci_addConfeeres -->
		<var name="ci_addConfeereci_listPtr"			expr="0"/>
		<var name="ci_addConfeereci_addedCount"		expr="0"/>
		<var name="ci_addConfeereci_rejCount"			expr="0"/>
		
		
	<!-- ci_confInProgress -->
		<!-- max length conferees may speak -->
		<!-- now set to 5 minutes -->
		<var name="ci_ConfMaxLength"					expr="300000"/>
		<var name="ci_ConfEndWait"						expr="60000"/>
		

<!-- ========================================= -->
<!-- call leg info. names, numbers, number of instances -->
<!-- ========================================= -->

<var name="destPhones"	expr="'<?php echo($_REQUEST["destPhones"])?>'"/>
<var name="destNames"	expr="'<?php echo($_REQUEST["destNames"])?>'"/>
<var name="confName"	expr="'<?php echo($_REQUEST["confName"]) ?>'"/>
<var name="confUniqueName"	expr="'<?php echo($_REQUEST["confUniqueName"]) ?>'"/>
<var name="conferenceName" expr="'VCMConferenceCall'"/>
<var name="NumberInstances"	expr="'<?php echo($_REQUEST["NumberInstances"])?>'"/>

<!-- ========================================= -->
<!-- session related -->
<!-- ========================================= -->

<var name="postSessionDelay" expr=" '<?php echo($postSessionDelay)?>' "/>
<var name="confSession" />	<!-- id of next session we create -->

<!-- ========================================= -->
<!-- URLs of VoiceXML scripts -->
<!-- ========================================= -->

<!-- ========================================= -->
<!-- URLs of CCXML scripts -->
<!-- ========================================= -->

<var name="ccxml_prefix" 		expr="'../ccxml/'"/>
<var name="ccxml_conf_legs"		expr="ccxml_prefix + '<?php echo($conf_legs)?>'	"/>



<!-- ========================================= -->
<!-- URLs of announcements -->
<!-- ========================================= -->
<!-- <var name="ann_conferece_over"		expr="'null://?text=The conference call is over. Please hang up now.'"/> -->

<!-- ========================================= -->
<!-- URLs of CGI scripts -->
<!-- ========================================= -->
<var name="url_event" expr="'basichttp'"/>


<!-- PROGRAM BEGINS -->


<log expr="'Voice Conference Manager Init Program (conf_init.php) begins...'"/>


<eventprocessor statevariable="ci_callState">

	<!-- ========================================= -->
	<!-- At load, create conference object -->
	<!-- ========================================= -->


	<transition state="ci_init" event="ccxml.loaded" name="evt">
		<log expr="'Loaded. Waiting for start signal from Higher Power.'"/>
	</transition>
	
	<transition state="ci_init" event="nextstate" name="evt">
		<assign name="ci_callState" expr="ci_makeConfObject" />
		<createconference conferenceid="confID" confname="conferenceName"/>
	</transition>

	
	<!-- ========================================= -->
	<!-- wait for conference to be created -->
	<!-- ========================================= -->

	<!-- no specific errors associated with "createconference" just yet -->
	<!-- since they may be defined in the future, stay in this state until we get confirmation -->

	<transition state="ci_makeConfObject" event="conference.created" name="evt">
		<!-- next state: make calls, add calls to conf object -->
		<var name="confObject" expr="evt.conference"/>
		<!--<log expr="'conference id of object created is ' + evt.conferenceid"/>
		<log expr="'type of event object is ' + typeof evt" />
		<log expr="'type of conf object is ' + typeof evt.conference" />
		<log expr="'conference.conferenceid is ' + confObject.conferenceid"/>
		<log expr="'conference.bridges length is ' + confObject.bridges.length"/>-->
		<assign name="ci_callState" expr="ci_confInProgress"/>
		<send data="nextstate" target="session.id"/>
	</transition>

	<transition state="ci_makeConfObject" event="error.conference*" name="evt">
		<!-- next state: make calls, add calls to conf object -->
		<log expr="'ERROR: &quot;' + evt.reason + '&quot; with id ' + confID + ', exiting now'"/>
		<!-- NOTE: If attendent is still connected, tell attendent! -->
		<exit/>
	</transition>

	<!-- ========================================= -->
	<!-- conference object available. start call legs -->
	<!-- ========================================= -->
		
	<transition state="ci_confInProgress" event="nextstate" name="evt">
		<!-- next state: make calls, add calls to conf object -->
		<var name="callTrack" expr="new Object()"/>
		<script>
			<![CDATA[
				callTrack.callList = new Array() ;
			]]>
		</script>
		<log expr="'type of callTrack object is ' + typeof callTrack" />
		<log expr="'type of callTrack.callList object is ' + typeof callTrack.callList" />
		<log expr="'value of callTrack.callList.length is ' + callTrack.callList.length" />
		<createccxml next="ccxml_conf_legs" sessionid="confSession" namelist="NumberInstances destPhones destNames confName confUniqueName confID confObject callTrack" />
	</transition>
	
	<transition state="ci_confInProgress" event="ccxml.created" name="evt">
		<!-- next state: make calls, add calls to conf object -->
		<assign name="ci_callState" expr="ci_Done"/>
		<send data="nextstate" target="session.id"/>
	</transition>

	<!-- error starting CCXML -->
	<transition state="ci_confInProgress" event="error.createccxml" name="evt">
		<log expr="'ERROR: in &quot;conf_main.xml&quot;, &quot;' + evt.reason + '&quot; while in state ' + ci_callState + ', exiting now'" />
		<exit/>
	</transition>

	
	<!-- error starting CCXML -->
	<transition state="ci_confInProgress" event="error.ccxml*" name="evt">
		<log expr="'ERROR: in &quot;conf_main.xml&quot;, &quot;' + evt.reason + '&quot; while in state ' + ci_callState + ', exiting now'" />
		<exit/>
	</transition>

	<!-- ========================================= -->
	<!-- Finished. Exit. -->
	<!-- ========================================= -->

	<transition state="ci_Done" event="nextstate" name="evt">
		<!-- next state: make calls, add calls to conf object -->
		<log expr="'Successful start of conference legs. Send start signal Exit VCM init'"/>
		<send data="nextstate" target="confSession" delay="postSessionDelay"/>
		<exit/>
	</transition>


<!--			<assign name="currentPhone" expr="phoneList.substr(11*ci_addConfeereci_listPtr,10)"/>
			<assign name="currentName" expr="labelList.split(nameDelimiter)[ci_addConfeereci_listPtr]"/>-->


	<!-- General purpose handlers -->
	
	

	<!-- General purpose error handlers -->
	
	<transition event="error.document" name="evt">
		<log expr="'ERROR: in &quot;conf_main.xml&quot;, &quot;' + evt.reason + '&quot; while in state ' + ci_callState + ', exiting now'" />
		<exit/>
	</transition>
	
	<transition event="error.system" name="evt">
		<log expr="'ERROR: &quot;' + evt.name + '&quot; with reason &quot;' + evt.reason + '&quot; while in state ' + ci_callState + ', exiting now'"/>
		<exit/>
	</transition>
	
	<transition event="error.*" name="evt">
		<log expr="'ERROR: &quot;' + evt.name + '&quot; with reason &quot;' + evt.reason + '&quot; while in state ' + ci_callState + ', exiting now'"/>
		<!-- makelist process may be waiting for us forever -->
		<exit/>
	</transition>
	
	<transition event="system.ping">
		<log expr="'ERROR: received system.ping, stop now'"/>
		<exit/>
	</transition>
	

</eventprocessor>

<!-- WARNING! Code found here will execute *before* eventprocessor! -->

</ccxml>
