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
		$Date: 2006-07-19 10:29:42 -0500 (Wed, 19 Jul 2006) $
		$Id: conference.php 81 2006-07-19 15:29:42Z myudkowsky $
		$Revision: 81 $
	*/
	
	// configuration items. Includes script names, delimiters, universal timeouts
	
	require("ccxml_config.inc");
	
	// PHP on P2006 requires this. Is it a configurable item?
	header('Cache-Control: no-cache');
	print ('<?xml version="1.0" encoding="UTF-8"?>') ;

?>
<ccxml version="1.0">

<log expr="'Conference call (conference.php) begins...'"/>

<!-- ========================================= -->
<!-- list of symbolic substate names: -->
<!-- ========================================= -->

<var name="s_init"					expr="'s_init'"/>
<var name="s_welcomeBase"			expr="'s_welcomeBase'"/>
<var name="s_getConfereeList"		expr="'s_getConfereeList'"/>
<var name="s_confCallWait"			expr="'s_confCallWait'"/>
<var name="s_confCallStart"			expr="'s_confCallStart'"/>
<var name="s_confCallErrorExit"		expr="'s_confCallErrorExit'"/>
<var name="s_Done"					expr="'s_Done'"/>
<var name="s_exit"					expr="'s_exit'"/>

<!-- ========================================= -->
<!-- user event names -->
<!-- ========================================= -->
<var name="info"					expr="'info'"/>
<var name="nextstate"				expr="'nextstate'"/>
<var name="calleriddata"			expr="'calleriddata'"/>
<var name="callerIDTimeout"			expr="'callerIDTimeout'"/>

<!-- ========================================= -->
<!-- Information passed to us by config file -->
<!-- ========================================= -->

<var name="NumberInstances"	expr="<?php echo($NumberInstances)?>"/>


<!-- ========================================= -->
<!-- Vars used throughout -->
<!-- ========================================= -->

	<!-- overall state name. Initialize: -->
	<var name="callState" expr="s_init"/>
	<var name="grammar_menu"/>

<!-- ========================================= -->
<!-- Variables used in various substates -->
<!-- ========================================= -->
	<!-- s_welcomeBase -->
		<var name="checkCallerID_timeout_count"		expr="0"/>
		<var name="checkCallerID_timeout_max"		expr="1"/>
	
	<!-- max number of permitted calls -->
		<var name="maxCalls"			expr="NumberInstances"/>
		

	<!-- s_addConfeeres -->
		<var name="s_addConfeeres_listPtr"			expr="0"/>
		<var name="s_addConfeeres_addedCount"		expr="0"/>
		<var name="s_addConfeeres_rejCount"			expr="0"/>
		
	
	<var name="confSession"	/>			<!-- session id of conf init -->

<!-- ========================================= -->
<!-- List of phone numbers, and associated names, to add to conference call -->
<!-- ========================================= -->

<var name="destPhones" expr="''"/>
<var name="destPhonesCount" expr="0"/>
<var name="destNames" expr="''"/>


<!-- ========================================= -->
<!-- URLs of VoiceXML scripts -->
<!-- ========================================= -->
<var name="vxml_prefix" expr="'../vxml/'"/>
<var name="vxml_type" expr="'application/voicexml+xml'"/>

<var name="vxml_conference" expr=" vxml_prefix + '<?php echo($conf_selections)?>'"/>
<var name="vxml_confstarted" expr=" vxml_prefix + '<?php echo($conf_started)?>' "/>
<var name="vxml_confStartError" expr=" vxml_prefix + '<?php echo($conf_error)?>' "/>


<!-- ========================================= -->
<!-- URLs of CCXML scripts -->
<!-- ========================================= -->
<var name="ccxml_prefix" expr="'../ccxml/'"/>
<var name="ccxml_conf_init" expr=" ccxml_prefix + '<?php echo($conf_init)?>' " />

<!-- ========================================= -->
<!-- URLs of CGI scripts -->
<!-- ========================================= -->
<var name="url_prefix" expr="''"/>
<var name="url_event" expr="'basichttp'"/>
<var name="url_CheckCallerID" expr="'<?php echo($checkCallerID)?>'"/>

<!-- ========================================= -->
<!-- Session variables -->
<!-- ========================================= -->
<var name="thisCall" expr="session.id"/>
<var name="thisSession" expr="session.id"/>
<var name="returnAddressURL" expr="'<?php echo($returnAddressURL)?>'"/>
<var name="returnAddressSession" expr="session.id"/>
<var name="thisConnectionID"/>	<!-- the attendant who makes this call -->
<var name="postSessionDelay" expr=" '<?php echo($postSessionDelay)?>' "/>
<var name="authorizationDelay" expr="'<?php echo($authorizationDelay)?>'"/>
<var name="confName" />
<var name="confUniqueName"/>

<!-- ========================================= -->
<!-- Error tracking variables -->
<!-- ========================================= -->
<var name="NumberErrors" expr="0" />
<var name="MaxNumberErrors"	expr="<?php echo($MaxNumberErrors)?>"/>
<var name="retry" expr="0"/>
<var name="MaxRetryErrors"	expr="<?php echo($MaxRetryErrors)?>"/>


<!-- ========================================= -->
<!-- PROGRAM BEGINS -->
<!-- ========================================= -->


<!-- ========================================= -->
<!-- On entry, get the caller id, which is used for user identification -->
<!-- ========================================= -->

<eventprocessor statevariable="callState">

	<!-- STATE s_init. Init the call * get it up and running -->
	
	<!-- Accept the call, check for database entry while accepting the call -->

	<transition state="s_init" event="connection.alerting" name="evt">
	
		<!-- save base call information -->
		<var	name="caller" expr="evt.connection.remote"/>
		<assign name="confName" expr="caller" />	<!-- use caller id for name of conference -->
		<assign name="confUniqueName" expr="caller + '<?php echo(mt_rand())?>'"/> <!-- generate unique name -->
		<log expr="'About to ask for caller ID auth check for caller ' + caller "/>
		<assign name="thisConnectionID" expr="evt.connectionid"/>

		<!-- We know who the caller is but not privileges if any -->
		<!-- send callerid and find privileges, list of names, etc. associated with caller -->
		<send target="url_CheckCallerID" targettype="url_event" name="'checkCallerID'" namelist="caller returnAddressURL returnAddressSession thisConnectionID"/>
		
		<send data="callerIDTimeout" target="thisSession" delay="authorizationDelay" />
	
		<!-- change to next state and await events -->
		<assign name="callState" expr="s_welcomeBase"/>
		
	</transition>

	
	<!-- If call disconnects -->
	
	<transition state="s_init" event="connection.disconnected" >
		<log expr="'Base call disconnected, exiting, state=' + callState" />
		<exit/>
	</transition>

	<!-- ========================================= -->
	<!-- STATE s_welcomeBase. Wait for caller-specific info, accept call -->
	<!-- ========================================= -->
	
	<!-- Receive data from external lookup -->
	<transition state="s_welcomeBase" event="calleriddata" name="evt">
		
		<!-- 1. check to see if data is valid -->
		<if cond="evt.valid != 'True'">
			<log expr="'ERROR: External database claims number not valid'"/>
			<!-- We need a less abrupt termination, such as an annoucement! -->
			<exit/>
		<else/>
			<!-- accept the call -->
			<accept connectionid="evt.thisConnectionID" />
		</if>
		
		<!-- 2. place data into variables with global scope -->
		<assign name="grammar_menu" expr="evt.grammar_menu"/>
		
		<!-- 3. move to next state -->
		<assign name="callState" expr="s_getConfereeList"/>

	</transition>

	<!-- send fails -->
	<transition state="s_welcomeBase" event="error.send.*" name="evt">
		<log expr="'ERROR: unable to reach authentication server, ' + evt.reason + ' eventid: ' + evt.eventid"/>
		<!-- we really need a much nicer way to terminate than just disconnecting -->
		<!-- we are not presently connected to call, caller will experience termination of ringing -->
		<!-- possible retry counter? -->
		<exit/>
	</transition>

	<!-- auth server does not respond -->
	<transition state="s_welcomeBase" event="callerIDTimeout" name="evt">
		<log expr="'ERROR: timed out. No response from authentication server'"/>
		<!-- we really need a much nicer way to terminate than just disconnecting -->
		<!-- we are not presently connected to call, caller will experience termination of ringing -->
		<!-- possible retry counter? -->
		<exit/>
	</transition>


	<!-- ========================================= -->
	<!-- After call connects, ask users for names of conferees -->
	<!-- ========================================= -->
	
	<transition state="s_getConfereeList" event="connection.connected" name="evt">
		<log expr="'Base call connected'"/>
		<dialogstart src="vxml_conference" type="vxml_type" namelist="grammar_menu maxCalls"/>
	</transition>

	<!-- Dialog finishes -->
	<transition state="s_getConfereeList" event="dialog.exit" name="evt">

		<log expr="'Found ' + evt.values.count + ' numbers, to wit: ' + evt.values.numberlist"/>
		<assign name="destPhones" expr="evt.values.numberlist"/>
		<assign name="destPhonesCount" expr="evt.values.count"/>
		<assign name="destNames" expr="evt.values.labellist"/>

		<if cond="destPhonesCount == 0 ">
			<!-- No names. Announce and exit. DEMO: just exit -->
			<assign name="callState" expr="s_exit"/>
			<!-- Get to next state -->
			<send data="nextstate" target="session.id" />

		<else/>
			<!-- start transition to next document -->
			<assign name="callState" expr="s_confCallWait"/>
			<createccxml sessionid="confSession" next="ccxml_conf_init" namelist="NumberInstances destPhones destNames confName confUniqueName" />
		</if>	

	</transition>

	<!-- ========================================= -->
	<!-- s_confCallWait. Start a new CCXML document that will handle the conference call -->
	<!-- ========================================= -->
	
	<!-- wait until available -->
	<transition state="s_confCallWait" event="ccxml.created" name="evt">
		<!-- tell our user that we have been successful -->
		<dialogstart src="vxml_confstarted" type="vxml_type" connectionid="thisConnectionID"/>
	</transition>

	<!-- done announcing -->
	<transition state="s_confCallWait" event="dialog.exit" name="evt">
		<!-- user notified of conf start. notify conf to start, exit -->
		<assign name="callState" expr="s_confCallStart"/>
		<send data="nextstate" target="session.id" />
	</transition>

	<!-- hung up while we are announcing, which we asked attendent to do -->
	<transition state="s_confCallWait" event="connection.disconnected" name="evt">
		<!-- user notified of conf start. notify conf to start, exit -->
		<assign name="callState" expr="s_confCallStart"/>
		<send data="nextstate" target="session.id" />
	</transition>


	<!-- error announcing -->
	<transition state="s_confCallWait" event="error.dialog*" name="evt">
		<!-- ignore error for this user -->
		<!-- get to next state -->
		<assign name="callState" expr="s_confCallStart"/>
		<send data="nextstate" target="session.id" />
	</transition>
	
	<!-- error starting CCXML -->
	<transition state="s_confCallErrorExit" event="error.createccxml" name="evt">
		<assign name="callState" expr="s_confCallErrorExit" />
		<send data="nextstate" target="session.id" />
	</transition>

	
	<!-- error starting CCXML -->
	<transition state="s_confCallErrorExit" event="error.ccxml*" name="evt">
		<assign name="callState" expr="s_confCallErrorExit" />
		<send data="nextstate" target="session.id" />
	</transition>
	
	<!-- ========================================= -->
	<!-- s_confCallStart. Tell new CCXML document that will handle the conference call -->
	<!-- ========================================= -->

	<transition state="s_confCallStart" event="nextstate" name="evt">
		<!-- tell our user that we have been successful -->
		<send data="nextstate" target="confSession" delay="postSessionDelay" />
	</transition>
	
	<transition state="s_confCallStart" event="send.successful" name="evt">
		<!-- ========================================= -->
		<!-- MAIN EXIT == we are finished -->
		<!-- ========================================= -->
		<exit/>
		<!-- ========================================= -->
	</transition>
	
	<!-- in case of error, retry. -->
	<transition state="s_confCallStart" event="error.send*" name="evt">
		<if cond="retry &lt; MaxRetryErrors">
			<!-- increment error counter/retry counter -->
			<send data="nextstate" target="confSession" />
			<assign name="retry" expr="Number(retry) + Number(1)"/>
			<log expr="'ERROR. Unable to send start signal because ' + evt.reason + '. Tried again.'"/>
		<else/>
			<log expr="'ERROR. FAILED! Unable to send start signal because ' + evt.reason"/>
			<exit/>
		</if>
	</transition>
	
	
	<!-- ========================================= -->
	<!-- s_exit. Graceful exit with error announcement  -->
	<!-- ========================================= -->
	
	
	
	<!-- tell the conf controller goodbye -->
	<transition state="s_exit" event="nextstate" name="evt">
		<!-- NOTE: This is not the right action if there are no calls to make! -->
		<dialogstart src="vxml_confStartError" type="vxml_type" connectionid="thisConnectionID"/>
		<assign name="callState" expr="s_exit"/>
	</transition>

	<!-- s_exit - Used to exit the application after a goodbye announcement -->
	<transition state="s_exit" event="dialog.exit" name="evt">
		<exit/>
	</transition>
	
	<!-- Even if there's a dialog error, exit anyway, prob. just means the conf controller hung up -->
	<transition state="s_exit" event="error.dialog*" name="evt">
		<exit/>
	</transition>

	<!-- ========================================= -->
	<!-- Error detected. Play error announcement, exit. -->
	<!-- ========================================= -->
	
	<transition state="s_confCallErrorExit" event="nextstate" name="evt">
		<dialogstart src="vxml_confStartError" type="vxml_type" connectionid="thisConnectionID"/>
	</transition>
	
	<transition state="s_confCallErrorExit" event="dialog.exit" name="evt">
		<exit/>
	</transition>
	
	<transition state="s_confCallErrorExit" event="error.dialog*" name="evt">
		<exit/>
	</transition>
	

	
	
	<!-- ========================================= -->
	<!-- General purpose handlers -->
	<!-- ========================================= -->
	
	<!-- ========================================= -->
	<!-- General purpose error handlers -->
	<!-- ========================================= -->
	
	<!-- Disconnect if caller disconnects -->
	<transition event="connection.disconnected" name="evt">
		<exit/>
	</transition>


	<transition event="call.CALL_INVALID">
		<log expr="'Invalid call, exiting, while in state ' + callState"/>
		<exit/>
	</transition>

	<transition event="error.document" name="evt">
		<log expr="'ERROR: in &quot;main.xml&quot;, &quot;' + evt.reason + '&quot; while in state ' + callState + ', exiting now'" />
		<exit/>
	</transition>
	
	<transition event="error.*" name="evt">
		<log expr="'ERROR: &quot;' + evt.reason + '&quot; while in state ' + callState + ', exiting now'"/>
		<exit/>
	</transition>
	
	<transition event="system.ping">
		<log expr="'ERROR: received system.ping, we must be dead'"/>
		<exit/>
	</transition>
	

</eventprocessor>

<!-- WARNING! Code found here will execute *before* eventprocessor! -->

</ccxml>
