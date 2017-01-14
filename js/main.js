var taskList = [];

var debugIDCounter = 0;
var debugVerbs = ["lick", "buzz", "water", "tick", "print", "poke", "request", "wail", "cry", "concentrate", "borrow", "heat", "smoke", "trick", "challenge", "snow", "form", "whine", "serve", "cause"];
var debugObjects = ["mirror", "rubber band", "shoes", "bow", "candle", "tissue box", "vase", "mp3 player", "drill press", "nail file", "teddies", "key chain", "pool stick", "newspaper", "car", "charger", "chocolate", "stop sign", "screw", "television"];

fillTaskListWithRandomData();
//fillTableWithRandomData();
fillTableWithTaskList();
finalizePage();

////////////////////////////////////////////////////////////////////////////////
//                                                                            //
////////////////////////////////////////////////////////////////////////////////

function btnDialogAbortClicked(){
	// $('#formCreateTaskItem').trigger("reset");
	$("#formName").val("")
	document.querySelector('#formIsImportant').MaterialCheckbox.uncheck()
	document.querySelector('#formIsUrgent').MaterialCheckbox.uncheck()
	
	document.querySelector('dialog').close();
}

function btnDialogOkClicked(){
	var item = createTaskItemFromForm();
	taskList.push(item);
	fillTableWithTaskList();
	$('#formCreateTaskItem').trigger("reset");
	
	document.querySelector('dialog').close();
}

function createTaskItemFromForm() {
	var item = {};
	
	item.id = debugIDCounter++;
	item.name = $("#formName").val();
	item.important = $("#formIsImportant").is('.is-checked');
	item.urgent = $("#formIsUrgent").is('.is-checked');
	
	return item;
}



function fillTaskListWithRandomData() {
	taskList = [];
	for (i=1; i<10; ++i) {
		taskList.push(createTaskItemRandomly());
	}
}

function createTaskItemRandomly() {
	var item = {};
	item.id = debugIDCounter++;
	
	var i = Math.floor(Math.random() * debugVerbs.length);
	var j = Math.floor(Math.random() * debugObjects.length);
	item.name = debugVerbs[i] + " " + debugObjects[j];
	debugVerbs.splice(i,1);
	debugObjects.splice(j,1);
	
	item.important = Math.random() > 0.5;
	item.urgent = Math.random() > 0.5;
	
	return item;
}

function fillTableWithTaskList() {
	var h = new Array(); i = 0;
	h[i++] = '<tr><th>';
	h[i++] = 'Id';
	h[i++] = '</th><th class="mdl-data-table__cell--non-numeric">';
	h[i++] = 'Name';
	h[i++] = '</th><th>';
	h[i++] = 'Important';
	h[i++] = '</th><th>';
	h[i++] = 'Urgent';
	h[i++] = '</th></tr>';
		
	
	var b = new Array(); j = 0;
	for (i=0; i<taskList.length; ++i){
		b[j++] ='<tr><td>';
		b[j++] = taskList[i].id;
		b[j++] = '</td><td>';
		b[j++] = taskList[i].name;
		b[j++] = '</td><td>';
		b[j++] = taskList[i].important;
		b[j++] = '</td><td>';
		b[j++] = taskList[i].urgent;
		b[j++] = '</td></tr>';
	}
	$('#taskList').html('<thead>'+h.join('')+'</thead><tbody>'+b.join('')+'</tbody>'); 
	console.log('items added');
}

function fillTableWithRandomData() {
	var h = new Array(); i = 0;
	h[i++] = '<tr><th class="mdl-data-table__cell--non-numeric">';
	h[i++] = 'Material';
	h[i++] = '</th><th>';
	h[i++] = 'Quantity';
	h[i++] = '</th><th>';
	h[i++] = 'Unit price';
	h[i++] = '</th></tr>';
	
	
	data = [
		['Acrylic (Transparent)', 25, '$2.90'],
		['Lorem', 50, '$2.15'],
		['Ipsum', 10, '$2.28'],
		['Dolor', 20, '$3.37'],
		['Sit', 30, '$4.46'],
		['Amet', 40, '$1.55'],
		['Foo', 60, '$5.64'],
		['Bar', 50, '$5.73'],
		['Plywood (Birch)', 50, '$1.25'],
		['Laminate (Gold on Blue)', 10, '$2.35']
	];
	
	
	var b = new Array(); i = 0;
	for (var key=0, size=data.length; key<size; key++){
		b[i++] ='<tr><td>';
		b[i++] = data[key][0];
		b[i++] = '</td><td class="whatever1">';
		b[i++] = data[key][1];
		b[i++] = '</td><td class="whatever2">';
		b[i++] = data[key][2];
		b[i++] = '</td></tr>';
	}
	$('#taskList').html('<thead>'+h.join('')+'</thead><tbody>'+b.join('')+'</tbody>'); 
	console.log('random data added');
}

function finalizePage() {
	// Polyfill dialog
	var dialog = document.querySelector('dialog');
    if (! dialog.showModal) {
      dialogPolyfill.registerDialog(dialog);
    }
    
   
	
	// Make table sortable
	$("#taskList").tablesorter(); 
	$("#myTable").tablesorter(); 
	
	// Make buttons clickable
	// var showDialogButton = document.querySelector('#btnAdd');
	// showDialogButton.addEventListener('click', function() {
      // dialog.showModal();
    // });
	$("#btnAdd").click(dialog.showModal);
	$("#btnDialogAbort").click(btnDialogAbortClicked);
	$("#btnDialogOk").click(btnDialogOkClicked);
	// dialog.querySelector('.close').addEventListener('click', function() {
      // ();
    // });
	
	
	console.log('finalized');
}