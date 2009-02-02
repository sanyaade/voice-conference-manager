// This module updates the status of the call in progress

// It parses the incoming text. If the row does not exist,
// the row is added. If the row does exist, the status cell
// is updated.

// $Header: /cvsroot/vcm/vcm/java/updateVcmStatus.js,v 1.1 2005/02/16 02:07:44 myudkowsky Exp $
// $Id: updateVcmStatus.js 51 2006-06-06 00:45:04Z myudkowsky $

var BROWSER_DELIMITER = ";" ;

var MESSAGE_STATUS = "Status" ;
var MESSAGE_DATA = "Data" ;

var MESSAGE_PREFIX = 0 ;
var CCXML_CALLID = 1 ;
var CCXML_NAME = 2 ;
var CCXML_PHONENUMBER = 3 ;
var CCXML_REPORT = 4 ;


var	ourTableName = "allCallStatus" ;

// Given name of table, find the table Node
function findTableNode (tableName) {
	tableList = document.getElementsByTagName("table") ;
	tableNode = tableList[tableName] ;	// type is Node
	
	// if table not present, create this table
	if ( tableNode == null){
		tableElement = createTable(tableName) ; // type is Element
		tableNode = tableElement ;		// subclass, after all
	}
	return tableNode ;
	
}


function createTable(tableName){

	// seperate from rest of tables, etc. with horizontal rule
	hrNode = document.createElement ("hr");
	toBodyAddElement ( hrNode ) ;

	// create and add table
	tableElement = document.createElement("table") ;
	tableElement.setAttribute ("name", tableName ) ;
	tableElement.setAttribute ("border", "1" ) ;
	toBodyAddElement (tableElement) ;
	
	// create caption for this table
	caption = document.createElement("caption") ;
	caption.innerHTML = tableName ;
	tableElement.appendChild(caption) ;
	
	// create headers in this table
	row = document.createElement("tr") ;

	cellList = [ "Name", "Number", "Status" ] ;
	
	for ( i=0 ; i < cellList.length; i++ ) {
		
		cell = document.createElement("td" ) ;
		cell.innerHTML = cellList[i] ;
		row.appendChild(cell) ;
	}
	
	// add row to table
	tableElement.appendChild(row) ;
	return tableElement ;
}

// add table to document
function toBodyAddElement (newElement){

	bodyNode = document.getElementsByTagName("body")[0] ;
	bodyNode.appendChild(newElement);
}


// add row to a given table, consisting of items in list
function addRow ( tableNode, rowName, cellList, cellNames ) {

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
	
	tableNode.appendChild(row) ;
	
	return 0 ;
}

function updateTable (inMessage) {

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
	
	
	tableName = params[CCXML_CALLID] + "-table" ;
	// tableName = "allCallStatus" ;
	phone = params[CCXML_PHONENUMBER];
	report = params[CCXML_REPORT] ;
	personName = params[CCXML_NAME] ;
	cellStatusName = phone + "-status" ;
	
	// statCell.innerHTML = inMessage ; // debugging
	
	// find, and create if need be, our table node
	
	tableNode = findTableNode(tableName) ;
	
	// rows in this table
	// tableNode might be a Node or Element, be careful
	rows = tableNode.getElementsByTagName("tr") ;
	// rows = tableNode.childNodes ; // NodeList. This formulation seems to return every row in document!
	
	rowName = phone ;	// convenient and probably unique


	// check to see if row in table exists
	
	rowNode = rows[rowName] ;	// should return just rows in this tableNode

	if ( rowNode == null) {
		
	
		// row with this name does not exist, so create it
		var cellList =  [ personName, phone, report ] ;
		var cellNames = [ null,       null,  cellStatusName ] ;
		result = addRow ( tableNode, rowName, cellList, cellNames ) ;

		// debug
		// outstring = listString(rows) ;
		// statCell.innerHTML = "rows names:  " + outstring ;
	
	} 
	else {
		// row already exists
		// just update the status cell
	
		cells = rowNode.getElementsByTagName("td") ;
		ourCell = cells[cellStatusName] ;
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
