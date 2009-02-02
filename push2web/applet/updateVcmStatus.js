// This module updates the status of the call in progress

// It parses the incoming text. If the row does not exist,
// the row is added. If the row does exist, the status cell
// is updated.

// $Author: myudkowsky $
// $Date: 2006-08-24 08:57:54 -0500 (Thu, 24 Aug 2006) $
// $Revision: 104 $
// $Id: updateVcmStatus.js 104 2006-08-24 13:57:54Z myudkowsky $

var BROWSER_DELIMITER = ";" ;

var MESSAGE_STATUS = "Status" ;
var MESSAGE_DATA = "Data" ;

var COMMAND_DROP		= "WebDrop" ;
var COMMAND_DROP_LABEL	= "Drop" ;
var COMMAND_MUTE		= "WebMute" ;
var COMMAND_MUTE_LABEL	= "Mute" ;
var COMMAND_UNMUTE		= "WebUnmute" ;
var COMMAND_MUTE_LABEL	= "Unmute" ;


var MESSAGE_PREFIX 		= 0 ;
var CCXML_CALLID 		= 1 ;
var CCXML_UNIQUEID 		= 2 ;
var CCXML_NAME 			= 3 ;
var CCXML_PHONENUMBER	= 4 ;
var CCXML_REPORT		= 5 ;
var CCXML_SESSIONID		= 6 ;

/////////////////////////
// Control button vars
/////////////////////////

var	buttonFlag = true ;		// set as overall param, but could in future be per-call

var muteImage = "mute.png" ;
var unmuteImage = "unmute.png" ;
var dropImage = "drop.png" ;
var buttonSeparator = "bar.png" ;

var muteHandlerFunc = "muteHandler" ;
var unmuteHandlerFunc = "unmuteHandler" ;
var dropHandlerFunc = "dropHandler" ;

// Mute button
var muteButton = new Object() ;
muteButton.imageURL = muteImage ;
muteButton.handler = muteHandlerFunc ;

// Unmute button
var unmuteButton = new Object() ;
unmuteButton.imageURL = unmuteImage ;
unmuteButton.handler = unmuteHandlerFunc ;

// Drop button
var dropButton = new Object() ;
dropButton.imageURL = dropImage ;
dropButton.handler = dropHandlerFunc ;

// list of buttons associated with every call
var buttonList = Array(muteButton, unmuteButton, dropButton) ;

var agt = navigator.userAgent.toLowerCase();
var ie = (agt.indexOf("msie") != -1) && (agt.indexOf("opera") == -1);

     

var moz = (agt.indexOf('mozilla')!=-1) && (agt.indexOf('spoofer')==-1)

                && (agt.indexOf('compatible') == -1) && (agt.indexOf('opera')==-1)

                && (agt.indexOf('webtv')==-1) && (agt.indexOf('hotjava')==-1);

     


///////////////////////
// DOM functions
///////////////////////


// Given name of table, find the table Node
function getTableNode (tableName, tableCaption) {
	tableList = document.getElementsByTagName("table");
	tableNode = document.getElementById(tableName); 
		
	//if table not present, create this table
	if( tableNode == null){
		tableElement = createTable(tableName, tableCaption) ; // type is Element
		tableNode = tableElement;
	}

	return tableNode ;
	
}


function createTable(tableName, tableCaption){

	var tableBody = document.createElement('tbody');

	// seperate from rest of tables, etc. with horizontal rule
	hrNode = document.createElement ("hr");
	toBodyAddElement ( hrNode ) ;

	// create and add table
	tableElement = document.createElement("table") ;
	tableElement.setAttribute ("name", tableName ) ;
	tableElement.setAttribute ("id", tableName ) ;

	if(ie) {
		tableElement.setAttribute ("className", "call" ) ;
	} else {
		tableElement.setAttribute ("class", "call" ) ;
	}

	toBodyAddElement (tableElement) ;
	
	// create caption for this table
	caption = document.createElement("caption") ;

	if(ie) {
		caption.setAttribute("className", "call" ) ;
	} else {
		caption.setAttribute("class", "call" ) ;
	}

	caption.innerHTML = "Call Information: " + tableCaption ;
	tableBody.appendChild(caption) ;
	
	// create headers in this table
	row = document.createElement("tr") ;

	cellList = [ "Name", "Number", "Status" ] ;
	if (buttonFlag) cellList.push("Control") ;
	
	for ( i=0 ; i < cellList.length; i++ ) {
		
		cell = document.createElement("th" ) ;
		if (cellList[i] == "Status") {
			cell.setAttribute("class","StatusHeader") ;
		}
		cell.innerHTML = cellList[i] ;
		row.appendChild(cell) ;
	}
	
	// add row to table
	tableBody.appendChild(row) ;
	
	tableElement.appendChild(tableBody);
	return tableElement ;
}

// add table to document
function toBodyAddElement (newElement){
	bodyNode = document.getElementsByTagName("body")[0] ;
	bodyNode.appendChild(newElement);
}

// add row to a given table, consisting of items in list
function addRow ( tableNode, rowName, cellList, cellNames, sessionid ) {

	// create row element
	row = document.createElement("tr") ;
	
	// add name to row
	row.setAttribute ("name", rowName ) ;
	
	// for each item in list of cells, create a cell and add to row
	
	for ( i=0 ; i < cellList.length; i++ ) {
		
		cell = document.createElement("td" ) ;
		// if cell has name, include that
		if (cellNames[i] != null) {
			cell.setAttribute ("name", cellNames[i] ) ;
		}
		cell.innerHTML = cellList[i] ;
		row.appendChild(cell) ;
	}
	
	// append the standard control buttons
	if (buttonFlag)
	{
		cell = document.createElement("td" ) ;
		cell.setAttribute ("name", rowName ) ;	// must be able to reference cell
		cell.setAttribute ("id", rowName ) ;	// must be able to reference cell

		
		for ( i = 0 ; i < buttonList.length ; i++ )
		{
			img = document.createElement("img") ;
			img.setAttribute ("src", buttonList[i].imageURL) ;
			tableID = tableNode
			handleString = buttonList[i].handler + "(\"" + sessionid + "\",\"" + rowName + "\")" ;

			if(ie) {
			   img["onclick"] = new Function(handleString);
			} else {
			   img.setAttribute ("onclick", handleString );
			}
			
			cell.appendChild(img) ;
			
			// add space if there's a following item
			if (i < buttonList.length -1 ) {
				img = document.createElement("img") ;
				img.setAttribute ("src", buttonSeparator) ;
				cell.appendChild(img) ;
			}
		}
		
		row.appendChild(cell) ;
	}
	
	tableNode.appendChild(row) ;
	
	return 0 ;
}

function updateTable (inMessage) {
	
	var rowNode = null;
	// DEBUG == DEBUG
	statCell = document.getElementById("LatestMessage");
	statCell.innerHTML = inMessage ;
	// DEBUG == DEBUG

	statCell = document.getElementById("ServerStatus"); // debug

	params = inMessage.split(BROWSER_DELIMITER) ;
	
	if ( params[MESSAGE_PREFIX] == MESSAGE_STATUS ) {
	
		// update table status 
		statCell.innerHTML = params[MESSAGE_PREFIX + 1] ;
		return ;
	}
	
	// otherwise, it's data
	
	
	tableName = params[CCXML_UNIQUEID] ;
	tableCaption =  params[CCXML_CALLID] ;
	phone = params[CCXML_PHONENUMBER];
	report = params[CCXML_REPORT] ;
	personName = params[CCXML_NAME] ;
	sessionid = params[CCXML_SESSIONID];
	cellStatusName = sessionid + "-status" ;
	
	// statCell.innerHTML = inMessage ; // debugging
	
	// find, and create if need be, our table node
	
	//Get the first child because it will be the tbody which is compatible in both ie and moz
	tableNode = getTableNode(tableName, tableCaption).firstChild ;
	
	// rows in this table
	// tableNode might be a Node or Element, be careful
	rows = tableNode.getElementsByTagName("tr") ;
	// rows = tableNode.childNodes ; // NodeList. This formulation seems to return every row in document!
	
	rowName = phone ;	// convenient and probably unique


	// check to see if row in table exists
	//rowNode = rows[rowName] ;	// should return just rows in this tableNode
	
	
	// check to see if extant row has same session id associated with it.
	// if not, then we should eliminate this row and replace it
	
// 	if ( rowNode != null ) {
// 		
// 		cells = rowNode.getElementsByName(cellStatusName) ;
// 		if (cells == null) {
// 			rows.pop(rowName) ;	// delete this row from list, no longer valid
// 			rowNode == null ;
// 	}

	//Find the correct row based on whether the client is moz or ie
	if(ie) {
		for(k = 0; k < rows.length; k++) {
			
			if(rows.item(k).attributes.getNamedItem("name") != null && rows.item(k).attributes.getNamedItem("name").value == rowName) {
				rowNode = rows.item(k);
				break;
			}
		}

	} else {
		rowNode = rows.namedItem(rowName);
	}

	if ( rowNode == null) {
		
	
		// row with this name does not exist, so create it
		var cellList =  [ personName, phone, report ] ;
		var cellNames = [ null,       null,  cellStatusName ] ;
		result = addRow ( tableNode, rowName, cellList, cellNames, sessionid ) ;

		// debug
		// outstring = listString(rows) ;
		// statCell.innerHTML = "rows names:  " + outstring ;
	
	} 
	else {
		// row already exists
		// just update the status cell
	
		cells = rowNode.getElementsByTagName("td") ;

		//Find the correct cell based on whether the client is ie or moz
		if(ie) {
			for(k = 0; k < cells.length; k++) {
			
				if(cells.item(k).attributes.getNamedItem("name") != null && cells.item(k).attributes.getNamedItem("name").value == cellStatusName) {
					ourCell = cells.item(k);
					
					break;
				}
			}

		} else {
			ourCell = cells.namedItem(cellStatusName);
		}

		ourCell.innerHTML = report ;

	}
}

// debugging function
// print out name attribute of each item in a list
function listString (list) {
	result = "Number items: " + list.length + ", type " + typeof(list) + " " ;
	for ( i = 0 ; i < list.length ; i++ ){
		result += list[i].getAttribute("name") + "\n" ;
	}
	return result ;
}

// for (key in array) is rumored to work over all keys
// try it sometime...

///////////////////////
// Command functions
///////////////////////

function muteHandler(sessionid, rowName) {

	outstring = 	MESSAGE_DATA  ;
	outstring +=	BROWSER_DELIMITER + sessionid ;
	outstring +=	BROWSER_DELIMITER + rowName ;
	outstring +=	BROWSER_DELIMITER + COMMAND_MUTE ;
	
	sendCommand(outstring) ;
	
}

function unmuteHandler(sessionid, rowName) {

	outstring = 	MESSAGE_DATA  ;
	outstring +=	BROWSER_DELIMITER + sessionid ;
	outstring +=	BROWSER_DELIMITER + rowName ;
	outstring +=	BROWSER_DELIMITER + COMMAND_UNMUTE ;
	
	sendCommand(outstring) ;
	
}

function dropHandler(sessionid, rowName) {
	
	outstring = 	MESSAGE_DATA  ;
	outstring +=	BROWSER_DELIMITER + sessionid ;
	outstring +=	BROWSER_DELIMITER + rowName ;
	outstring +=	BROWSER_DELIMITER + COMMAND_DROP ;
	
	sendCommand (outstring) ;

}

function sendCommand (outstring) {
	
	// DEBUG == DEBUG
	statCell = document.getElementById("LatestMessage");
	statCell.innerHTML = outstring ;
	// DEBUG == DEBUG
	
	// send to Java applet
	document.applets.ConnectToServer.receiveDataFromJS(outstring);
}
