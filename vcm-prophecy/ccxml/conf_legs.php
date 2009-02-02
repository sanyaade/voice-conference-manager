<?php

	/*
		Handle a leg of a conference call.
		
		Each leg proceeds independently of all other legs.
		There is no session manager.
		The number of legal legs is a function of the number of licesnes.
		
		Logic:
			Since there's no session manager, each leg creates a successive leg
			until all legs have been created.
			
			Each leg will proceed independently until hangup.
			
			No reporting of status to external servers. We may in the future report
			status to this server, and make that information available.
	*/


	/*
		Copyright (c) 2004-2006 by M. Yudkowsky, Disaggregate http://www.Disaggregate.com
		See attached license for details and disclaimers

		$Author: myudkowsky $
		$Date: 2006-07-19 10:29:42 -0500 (Wed, 19 Jul 2006) $
		$Id: conf_legs.php 81 2006-07-19 15:29:42Z myudkowsky $
		$Revision: 81 $
	*/
	
	// configuration items. Includes script names, delimiters, universal timeouts
	
	require("ccxml_config.inc"); 
	
	// PHP on P2006 requires this. Is it a configurable item?
	header('Cache-Control: no-cache');
	print ('<?xml version="1.0" encoding="UTF-8"?>') ;


?>

<ccxml version="1.0">

<log expr="'Call Leg (conf_leg.php) begins...'"/>


<!-- Valid states for this call -->

<!-- list of symbolic substate names: -->
<var name="cl_init"					expr="'cl_init'"/>
<var name="cl_startNextLeg"			expr="'cl_startNextLeg'"/>
<var name="cl_call_start"			expr="'cl_call_start'"/>
<var name="cl_call_InProgress"		expr="'cl_call_InProgress'"/>
<var name="cl_conf_InProgress"		expr="'cl_conf_InProgress'"/>
<var name="cl_dropout_start"		expr="'cl_dropout_start'"/>
<var name="cl_dropout_finish"		expr="'cl_dropout_finish'"/>
<var name="cl_MuteInProgress"		expr="'cl_MuteInProgress'"/>
<var name="cl_UnmuteInProgress"		expr="'cl_UnmuteInProgress'"/>
<var name="cl_conf_WebDropout"		expr="'cl_conf_WebDropout'"/>

<!-- event names -->
<var name="dropout"					expr="'dropout'"/>
<var name="teardown"				expr="'teardown'"/>
<var name="nextstate"				expr="'nextstate'"/>
<var name="ringing"					expr="'ringing'"/>
<var name="conferenced"				expr="'conferenced'"/>
<var name="announcing"				expr="'announcing'"/>
<var name="dropped"					expr="'dropped'"/>
<var name="confDelete"				expr="'confDelete'"/>
<var name="mute"					expr="'mute'"/>
<var name="unmute"					expr="'unmute'"/>
<var name="rejoin"					expr="'rejoin'"/>
<var name="leftConference"			expr="'leftConference'"/>


<!-- attribute value -->
<var name="DUPLEX_HALF"				expr="'half'"/>
<var name="DUPLEX_FULL"				expr="'full'"/>

<!-- Web-based event names -->
<var name="WebDrop"					expr="'WebDrop'"/>
<var name="WebMute"					expr="'WebMute'"/>
<var name="WebUnmute"				expr="'WebUnmute'"/>


<!-- ========================= -->
<!-- Global variables -->
<!-- ========================= -->

	<?php
	
		/*
			Given the POST variables, extract destPhone, etc.
		*/
		
		// split destPhones into list of phone numbers
		$destPhones = split($delimiter, $_REQUEST["destPhones"]) ;
		
		// first on list is our phone
		$destPhone = array_shift($destPhones);
		if ($destPhones != NULL )
			$destPhones = implode($delimiter, $destPhones) ;
		
		// split destNames into list of phone numbers
		$destNames = split($delimiter, $_REQUEST["destNames"]) ;

		// first on list is our name
		$destName = array_shift($destNames);
		if ( $destNames != NULL )
			$destNames = implode($delimiter,$destNames) ;	// back into string for xfer onwards
			
	?>
	
	<!-- overall state name. Initialize: -->
	<var name="cl_callState" expr="cl_init"/>
	
	<!-- number to call -->
	<var name="destPhone"	expr="'<?php echo($destPhone)?>'"/>
	
	<!-- name we called -->
	<var name="destName"	expr="'<?php echo($destName)?>'"/>
	
	<log expr="'Making call to ' + destName"/>
	
	<!-- conference object ID -->
	<var name="confID"		expr="'<?php echo($_REQUEST["confID"])?>'"/>
	<var name="confObject"	expr="'<?php echo($_REQUEST["confObject"])?>'"/>
	
	<!-- Human-readable name, used to display call progress on browser -->
	<var name="confName"	expr="'<?php echo($_REQUEST["confName"])?>'"/>
	<var name="report"		expr="''" />
	
	<!-- unique ID for this conference, used by browser to sort different calls -->
	<var name="confUniqueName"	expr="'<?php echo($_REQUEST["confUniqueName"])?>'"/>

	
	<!-- this call's ID -->
	<var name="thisSession"	expr="session.id"/> <!-- is session.id available at this point? -->
	<var name="thisCall"/>
	
	<!-- Number of errors encountered in script -->
	<var name="NumberErrors" expr="0" />
	
<!-- ========================= -->
<!-- variables related to overall management of sessions -->
<!-- ========================= -->
	
	<!-- session ID of any instance we create -->
	<var name="nextInstance" expr="''"/>
	
	<?
		$priorInstance = $_REQUEST["currentInstance"] ;
		// decrement number of instances each time this leg starts
		$NumberInstances = $_REQUEST["NumberInstances"] - 1 ;
	?>
	
	<!-- session ID of any prior instances in the chain -->
	<var name="priorInstance" expr="'<?php echo($priorInstance)?>'" />
	
	<!-- other session-related tracking vars -->
	<var name="NullSession"	expr="'NoChainLink'"/>
	<var name="newNext"		expr="NullSession" />
	<var name="newPrior"	expr="NullSession" />

	
	<!-- session ID of this instance, to send onwards -->
	<var name="currentInstance" expr="session.id"/>
	
	<!-- number of instances left to create -->
	<var name="NumberInstances" expr="'<?php echo($NumberInstances)?>'"/>
	
	<!-- list of phone numbers to call -->
	<var name="destPhones" expr="'<?php echo($destPhones)?>'"/>

	<!-- list of names of people on the call -->
	<var name="destNames" expr="'<?php echo($destNames)?>'"/>
	
	<!--<var name="callTrack" expr="'<?php echo($_REQUEST["callTrack"])?>'" />-->
<!--	<script>
		<![CDATA[
		callTrack = <?php echo($_REQUEST["callTrack"])?> ;
		]]>
	</script>-->
	<var name ="callsLeft" expr="true" />
	<var name="totalUndefined" expr="0" />
	
	<var name="postSessionDelay" expr="'<?php echo($postSessionDelay)?>'"/>
	
	<var name="muteDelay" expr="'<?php echo($muteDelay)?>'"/>	<!-- effort to avoid unjoin/join problem -->
	
	<var name="cl_callState_announce" expr="'<?php echo($conf_over)?>'"/>	<!-- what announement do we play on drop out? -->


<!-- ========================= -->
<!-- Variables used in various substates -->
<!-- ========================= -->

		
	<!-- cl_dropout -->
	<!-- assign default value -->
	<var name="cl_dropout_announce"		expr="'<?php echo($conf_over)?>'"/>

<!-- ========================= -->
<!-- URLs of CCXML scripts -->
<!-- ========================= -->

	<var name="conf_legs" expr="'<?php echo($conf_legs)?>'"/>

<!-- ========================= -->
<!-- URLs of VoiceXML scripts -->
<!-- ========================= -->

	<!-- Common prefixs, types -->
	<var name="vxml_prefix" expr="'../vxml/'"/>
	<var name="vxml_type" expr="'application/voicexml+xml'"/>
	<var name="announcement_type" expr="'audio/wav'"/>
	
	<!-- Annoucements -->
	
	<var name="vxml_youarejoining"	expr="vxml_prefix + '<?php echo($conf_join)?>'	"/>
	<var name="vxml_conf_error"		expr="vxml_prefix + '<?php echo($conf_err)?>'	"/>
	<var name="vxml_conf_dropped"	expr="vxml_prefix + '<?php echo($conf_dropped)?>'	"/>
	<var name="vxml_conf_over"		expr="vxml_prefix + '<?php echo($conf_over)?>'	"/>

	<!-- HTTP target -->
	<var name="url_event" expr="'basichttp'"/>	


<!-- PROGRAM BEGINS -->

<!--<log expr="'Instance of conf_legs.php pre-state loaded'"/>-->

<eventprocessor statevariable="cl_callState">

	<!-- ========================= -->
	<!-- on init, create any subsequent legs -->
	<!-- ========================= -->
	
	
	<transition state="cl_init" event="ccxml.loaded" name="evt">
		<log expr="'Instance of conf_legs.php loaded'"/>
	</transition>
	
	<!-- nextstate transition will arrive from another session, not from this one -->
	<transition state="cl_init" event="nextstate" name="evt">

		<log expr="'Instance of conf_legs.php starting'"/>

		<!-- first concern: create next legs of call if needed -->
		<if cond="NumberInstances &gt; 0">
			<!-- Start copy of that script. Use namelist to send variables -->
			<createccxml next="conf_legs" sessionid="confSession" namelist="NumberInstances destPhones destNames confName confUniqueName confID currentInstance confObject callTrack" />
		
		<else/>
			<!-- no other legs to start -->
			<assign name="cl_callState" expr="cl_call_start"/>
			<send data="nextstate" target="session.id"/>
		</if>

	</transition>
	
	<!-- if created above, wait until CCXML script starts -->
	<transition state="cl_init" event="ccxml.created" name="evt">
		
		<!-- save session id of next state in case we have to send it emergency teardown message -->
		<assign name="nextInstance" expr="evt.sessionid"/>
		<!-- tell next state to start -->
		<send data="nextstate" target="nextInstance"/>

		<assign name="cl_callState" expr="cl_call_start"/>
		<send data="nextstate" target="session.id"/>
	</transition>

	<!-- if it fails to start: log, inform rest of chain, begin exit -->
	<transition state="cl_init" event="error.createccxml" name="evt">
		<log expr="'ERROR: &quot;' + evt.reason + '&quot;. Exiting CCXML at cl_init. Unable to start next call leg.'"/>
		<!-- inform prior instances in chain if unable to create rest of chain -->
		<if cond="priorInstance != '' " >
			<send data="teardown" target="priorInstance"  namelist="thisSession"/>
		</if>
		<send data="teardown" target="session.id"  namelist="thisCall"/>
	</transition>

	<!-- ========================= -->
	<!-- Ready to start outbound call -->
	<!-- ========================= -->

	<transition state="cl_call_start" event="nextstate" name="evt">
		<createcall dest="destPhone" callerid="'<? echo ($callerid)?>'"/>
	</transition>
	
	<!-- send updates on call state to any interested observer, if enabled -->
	<transition state="cl_call_start" event="connection.alerting">
		<?php sendPush("alerting");?>
	</transition>
	<transition state="cl_call_start" event="connection.progressing">
		<?php sendPush("progressing");?>
	</transition>
	

	<!-- when connected, play welcome message -->
	<transition state="cl_call_start" event="connection.connected" name="evt" >
		
		<?php sendPush("announcing");?>
		<assign name="thisCall" expr="evt.connectionid"/>
		<dialogstart connectionid="thisCall" src="vxml_youarejoining" type="vxml_type"/>
		<!-- go to call-in-progress and wait for announcement to finish -->
		<assign name="cl_callState" expr="cl_call_InProgress"/>
		
	</transition>
	
	<!-- note: we are delibreately ignoring call legs that end in failure -->
	<!-- we could give an informative message, but what would that message be? -->
	<!-- after all, without a session manager, we don't know how many sessions are up -->
	
	<!-- ========================= -->
	<!-- Add call into conference -->
	<!-- ========================= -->
	
	<!-- announcement in progress -->
	<transition state="cl_call_InProgress" event="dialog.started"/>
	
	<!-- when announcement finishes -->
	<transition state="cl_call_InProgress" event="dialog.exit">
		<join id1="thisCall" id2="confID" exittone="false"/>
	</transition>
	
	<!-- conference joined -->
	<transition state="cl_call_InProgress" event="conference.joined">
		<?php sendPush("in conference");?>
		<!--<assign name="callTrack.callList[thisCall]" expr="true" />-->
		<assign name="cl_callState" expr="cl_conf_InProgress" />
		<!--<log expr="'number of connections is ' + confObject.bridges.length"/>-->
	</transition>

	<!-- any conference failure -->
	<transition state="cl_call_InProgress" event="error.conference*">
		<dialogstart connectionid="thisCall" src="vxml_conf_error" type="vxml_type"/>
		<!-- when dialog is over, be in 'exiting' state -->
		<assign name="cl_callState" expr="cl_dropout_finish" />
		<?php sendPush("conference failure");?>
	</transition>


	<!-- ========================= -->
	<!-- Conference in progress -->
	<!-- ========================= -->

	<!-- Request to end call (from up or down chain) -->
	<transition state="cl_conf_InProgress" event="teardown">
		
		<!-- send out teardowns upstream and downstream -->
		<if cond="priorInstance != ''">
			<send data="teardown" target="priorInstance" namelist="thisSession"/>
		</if>
		<if cond="nextInstance != ''">
			<send data="teardown" target="nextInstance" namelist="thisCall"/>
		</if>
		
		<!-- get ourselve out of conference to make announcement -->
		<unjoin id1="thisCall" id2="confID"/>
		<?php sendPush("leaving conference");?>

	</transition>

	<!-- Request to end call (from web) -->
	<transition state="cl_conf_InProgress" event="dropout">	
		<assign name="cl_callState" expr="cl_conf_WebDropout" />
		<send data="nextstate" target="session.id"/>
	</transition>

	<!-- out of conference. Start dropout announcement -->
	<!-- NOTE: we need to know how to handle unjoin better. -->
	<!-- 	distinguish between unjoin where conference exits? -->
	<!-- NOTE: this is the place to destroy the conference object -->
	<transition state="cl_conf_InProgress" event="conference.unjoined">
	
		<?php sendPush("out of conference");?>
		
		<!-- notify locally and globally that we are out of call -->
		<send data="leftConference" target="session.id" namelist="thisCall newNext newPrior" />
		
		<dialogstart connectionid="session.id" src="vxml_conf_error" type="vxml_type"/>
		
		<!-- when dialog is over, be in exiting state -->
		<assign name="cl_callState" expr="cl_dropout_finish" />
		
	</transition>
	
	<transition state="cl_conf_InProgress" event="mute">
		<assign name="cl_callState" expr="cl_MuteInProgress"/>
		<send data="nextstate" target="session.id"/>
	</transition>

	<transition state="cl_conf_InProgress" event="unmute">
		<assign name="cl_callState" expr="cl_UnmuteInProgress"/>
		<send data="nextstate" target="session.id"/>
	</transition>


	<!-- ========================= -->
	<!-- Mute call -->
	<!-- ========================= -->

	<transition state="cl_MuteInProgress" event="nextstate">
		<!-- step 1. Unjoin from the conference call, no exittone -->
		<unjoin id1="thisCall" id2="confID"/>
	</transition>

	<transition state="cl_MuteInProgress" event="conference.unjoined" name="evt" >
		<!-- step 2a. Attempt to work around problem where immediate join does not return -->
		<send data="rejoin" target="session.id" delay="muteDelay"/>
	</transition>

	<transition state="cl_MuteInProgress" event="rejoin" name="evt" >
		<!-- step 2. Rejoin with no entry tone -->
		<join id1="thisCall" id2="confID" entertone="false" exittone="false" duplex="DUPLEX_HALF"/>
	</transition>
	
	<transition state="cl_MuteInProgress" event="conference.joined" name="evt" >
		<?php sendPush("in conference (muted)");?>
		<assign name="cl_callState" expr="cl_conf_InProgress" />
	</transition>

	<!-- ========================= -->
	<!-- Unmute call -->
	<!-- ========================= -->

	<transition state="cl_UnmuteInProgress" event="nextstate">
		<!-- step 1. Unjoin from the conference call, no exittone -->
		<unjoin id1="thisCall" id2="confID"/>
	</transition>

	<transition state="cl_UnmuteInProgress" event="conference.unjoined" name="evt" >
		<!-- step 2. Rejoin with no entry tone -->
		<join id1="thisCall" id2="confID" entertone="false" exittone="false" duplex="DUPLEX_FULL"/>
	</transition>
	
	<transition state="cl_UnmuteInProgress" event="conference.joined" name="evt" >
		<?php sendPush("in conference (unmuted)");?>
		<assign name="cl_callState" expr="cl_conf_InProgress" />
	</transition>

	<!-- Note: how should unjoin errors be handled? -->
	
	<!-- ========================= -->
	<!-- User dropped from call by Web request -->
	<!-- ========================= -->

	<!-- Request to end call (from web) -->
	<transition state="cl_conf_WebDropout" event="nextstate">
		<assign name="cl_callState"				expr="cl_dropout_start" />
		<assign name="cl_callState_announce"	expr="vxml_conf_dropped" />
		<send data="dropout" target="session.id"/>
	</transition>

	<!-- ========================= -->
	<!-- Web-enabled requests  -->
	<!-- ========================= -->
	<transition event="WebDrop" name="evt" >
		<send data="dropout" target="session.id"/>
		<?php sendPush("dropping");?>
	</transition>

	<transition event="WebMute" name="evt" >
		<send data="mute" target="session.id"/>
		<?php sendPush("muting");?>
	</transition>

	<transition event="WebUnmute" name="evt" >
		<send data="unmute" target="session.id"/>
		<?php sendPush("un-muting");?>
	</transition>

	<!-- ========================= -->
	<!-- App-requested droputs  -->
	<!-- ========================= -->

	
	<!-- unjoin from the conference object, otherwise you cannot make annoucement -->
	<transition state="cl_dropout_start" event="dropout" name="evt" >
		<unjoin id1="thisCall" id2="confID"/>
		<?php sendPush("leaving conference");?>
	</transition>
	
	<!-- dropout announcement start -->
	<transition state="cl_dropout_start" event="conference.unjoined" name="evt" >
		<?php sendPush("out of conference");?>
		<assign name="cl_callState" expr="cl_dropout_finish" />
		<dialogstart connectionid="thisCall" src="cl_callState_announce" type="vxml_type"/>
	</transition>
	
	<!-- dropout announcement start if we are not in conference state -->
	<!-- ignore the error, assume we were never in conference -->
	<transition state="cl_dropout_start" event="error.conference.unjoin" name="evt" >
		<!-- notify locally and globally that we are out of call -->
		<send data="leftConference" target="session.id" namelist="thisCall newNext newPrior" />
		<assign name="cl_callState" expr="cl_dropout_finish" />
		<dialogstart connectionid="thisCall" src="cl_callState_announce" type="vxml_type"/>
		<?php sendPush("disconnecting");?>
	</transition>
		
	<!-- dropout completed -->
	<transition state="cl_dropout_finish" event="dialog.exit" name="evt" >
		
		<log expr="'Conferee ID ' + destPhone + '/' + thisCall + ' dropping out '" /> <!-- should be "connectionid" -->
		
		<?php sendPush("dropped");?>
		<disconnect connectionid="thisCall"/>

	</transition>
	
	<transition state="cl_dropout_finish" event="connection.disconnected" name="evt" >
		<exit/>
	</transition>
	
	
	<!-- dropout completed -->
	<transition state="cl_dropout_finish" event="dropout" name="evt" >
		
		<?php sendPush("dropped");?>
		<log expr="'Conferee ' + destPhone + '/' + thisCall + ' dropping out '" />
		<exit/>

	</transition>
	
	<!-- ======================================= -->
	<!-- track number of calls in conference -->
	<!--  (this is a work-around for lack of conference object) -->
	<!-- ======================================= -->
		
	<transition event="leftConference" name="evt">

		<!-- a call has left the conference -->
		<script>
		<![CDATA[
			
			// remove this call from list of calls
// 			callTrack.callList[evt.callTrack] = undefined ;
			
			// see how many calls are left on the call
			totalUndefined = 0 ;
// 			for (i=0; i<callTrack.length; i++) {

// 			for ( i in callTrack.callList  ) {

/*				if (callTrack[i] == evt.thisCall) {
					callTrack[i] = undefined ;	// remove this call
				}
				if (callTrack[i] == undefined) {
					totalUndefined += 1 ;		// sum of all calls
				}*/
				
/*				if (callTrack.callList[i] == undefined) totalUndefined += 1 ;*/
				
/*			}*/
			
			// is this the last remaining call?
// 			if ( totalUndefined < (callTrack.callList.length - 1) ) {
// 				callsLeft = true ;
// 			}
// 			else {
// 				callsLeft = false ;	// this is the final leg of call
// 			}
		]]>
		</script>
		
		<!-- reset to original values -->
		<assign name="newPrior"	expr="NullSession" />
		<assign name="newNext"	expr="NullSession" />
	
		<!-- did this conference departure originate locally? -->
		<if cond="evt.thisCall != thisCall" >
		
			<!-- If this did not original locally, then we have to pass -->
			<!--   the warning upstream and downstream -->
			<!-- Check to make certain that we are not passing it back to originator -->

			<if cond="(priorInstance != evt.sessionid) &amp;&amp; (priorInstance !='') ">
				<send data="leftConference" target="priorInstance" namelist="thisCall newNext newPrior"/>
			</if>
			<if cond="nextInstance != evt.sessionid &amp;&amp; (nextInstance !='') ">
				<send data="leftConference" target="nextInstance" namelist="thisCall newNext newPrior"/>
			</if>
			
			<!-- If the call did not originate locally, we may have to reset the chain -->
			
			<if cond="evt.newNext != NullSession">
				<assign name="nextInstance" expr="evt.newNext" />
			</if>
			<if cond="evt.newPrior != NullSession">
				<assign name="priorInstance" expr="evt.newPrior" />
			</if>

		<else/>
		
			<!-- if this leftConference originated locally, we are about to drop out -->
			<!-- inform next and previous instances so they can heal -->
			
			<if cond="priorInstance != ''">
				<assign name="newNext" expr="nextInstance"/>
			</if>
			<if cond="nextInstance != ''">
				<assign name="newPrior" expr="priorInstance"/>
			</if>
			
			<!-- actually send notification -->
			<send data="leftConference" target="priorInstance" namelist="thisCall newNext newPrior"/>
			
		</if>
		
		<!-- reset values -->
		<assign name="newPrior"	expr="NullSession" />
		<assign name="newNext"	expr="NullSession" />

		<!-- Finally, check to see if we should delete conference object -->
		<send data="confDelete" target="session.id"/>
		
	</transition>


	<!-- ========================= -->
	<!-- disconnects with graceful exits -->
	<!-- ========================= -->
	
	<!-- if they disconnect voluntarily at any time -->

	<transition  event="connection.disconnected" name="evt" >
		<?php sendPush("disconnected");?>
		<!-- notify locally and globally that we are out of call -->
		<send data="leftConference" target="session.id" namelist="thisCall newNext newPrior" />
		<assign name="cl_callState" expr="cl_dropout_finish" />
		<send data="dropout" target="session.id" namelist="thisCall" delay="postSessionDelay"/>
	</transition>	
	
	<transition  event="connection.failed" name="evt" >
		<!-- possibly destroy conference -->
		<?php sendPush("connection failed");?>
		<!-- notify locally and globally that we are out of call -->
		<send data="leftConference" target="session.id" namelist="thisCall newNext newPrior" />
		<log expr="'Connection failed: &quot;' + evt.reason + '&quot; while in state ' + cl_callState "/>
		<assign name="cl_callState" expr="cl_dropout_finish" />
		<send data="dropout" target="session.id" namelist="thisCall" delay="postSessionDelay"/>
	</transition>	

	
	<!-- If there's a failure, handle it as a dropout -->
	<transition  event="connection.failed" name="evt" >
		<?php sendPush("connection failed");?>
		<!-- notify locally and globally that we are out of call -->
		<send data="leftConference" target="session.id" namelist="thisCall newNext newPrior" />
		<assign name="cl_callState" expr="cl_dropout_finish" />
		<send data="dropout" target="session.id" namelist="thisCall" delay="postSessionDelay"/>
	</transition>

	<!-- asked to drop call. goto dropout -->
	<transition  event="teardown" name="evt" >
		<log expr="'Conferre ID ' + destPhone + '/' + session.id + ' tearing down '" />
		
		<!-- get announcement, if any provided -->
		<assign name="cl_callState_announce" expr="evt.data" />
	
		<if cond="cl_callState_announce != '' " >
			<!-- announcement to play -->
			<assign name="cl_callState" expr="cl_dropout_start" />
		<else/>
			<!-- just drop out. Note: perhaps a default announcement? -->
			<assign name="cl_callState" expr="cl_dropout_finish" />
		</if>
		<!-- notify locally and globally that we are out of call -->
		<send data="leftConference" target="session.id" namelist="thisCall newNext newPrior" />
		<send data="dropout" target="session.id" namelist="thisCall" delay="postSessionDelay" />

	</transition>
	
	<!-- we are dropping out -->
	<transition  event="dropout" name="evt" >
		<?php sendPush("dropped out");?>
		<log expr="'Conferre ID ' + destPhone + '/' + thisCall + ' dropout-&gt;exit'" />
		<exit/>

	</transition>	

	<!-- ========================= -->
	<!-- General purpose error handlers -->
	<!-- perhaps less graceful exits -->
	<!-- ========================= -->
	
	<transition event="confDelete" name="evt">
		<if cond="callsLeft == false">
			<destroyconference conferenceid="confID"/>
		</if>
	</transition>
	
	<!-- ignore errors in attempted destruction of conference object -->
	<!-- after all someone else may still be on the call -->
	<transition event="error.conference.destroy">
		<log expr="'ERROR? Unable to destroy conference because &quot;' + evt.reason + '&quot; while in state ' + cl_callState " />
	</transition>
	
	<transition event="conference.destroyed">
		<log expr="'Destroyed conference while in state ' + cl_callState " />
	</transition>


	<transition event="error.document" name="evt">
		<log expr="'ERROR: in &quot;conf_legs.php&quot;, &quot;' + evt.reason + '&quot; while in state ' + cl_callState + ', exiting now'" />
		<exit/>
	</transition>
	
	<transition event="error.send.failed" name="evt">
		<log expr="'ERROR: in &quot;conf_legs.php&quot;, &quot;' + evt.reason + '&quot; while in state ' + cl_callState + ', ignoring SEND error!'" />
	</transition>
	
	
	<!-- explicity ignore for the meantime -->
	<transition event="send.successful" />
	
	
	<transition event="error.*" name="evt">
		<log expr="'ERROR: &quot;' + evt.reason + '&quot; while in state ' + cl_callState "/>
		<!-- <assign name="connection" expr="session.id"/> -->
		<!-- Check to see if there are too many errors. If yes, stop immediately -->
		<assign name="NumberErrors" expr="NumberErrors = Number(NumberErrors) + Number(1)"/>
		<if cond="NumberErrors &gt; 1">
			<log expr="'ERROR: Too many general errors, exit immediately'"/>
			<exit/>
		<else/>
			<send data="dropout" target="session.id" namelist="thisCall"/>
		</if>
	</transition>
	
	<!-- sick puppy -->
	<transition event="system.ping">
		<log expr="'ERROR: received system.ping, exit immediately'"/>
		<exit/>
	</transition>
	

</eventprocessor>

<!-- WARNING! Code found here will execute *before* eventprocessor! -->

</ccxml>
