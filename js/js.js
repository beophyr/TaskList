/**javascript file for communication with api
 * 
 * **/
var apiurl="api.php";

//some flags
var setcredentials=true;
var installLibs=true;

$(document).ready(runInit());

function showLoadingDialog(message){
	$("#dialog_loading").remove();
	$('<dialog class="mdl-dialog" id="dialog_loading"><h4 class="mdl-dialog__title"></h4><div class="mdl-dialog__content"><div  class="mdl-progress mdl-js-progress mdl-progress__indeterminate"></div></div></dialog>').appendTo("body");
	$("#dialog_loading").find("h4.mdl-dialog__title").html(message);
	
	var dialog = document.querySelector('#dialog_loading');
    if (! dialog.showModal) {
      dialogPolyfill.registerDialog(dialog);
    }
    
    dialog.showModal();
	
}

function hideLoadingDialog(){
	document.querySelector('#dialog_loading').close();
}

function runInit(){
	showLoadingDialog("Initializing");
    
    $.getJSON(apiurl, function(data) {
    	if(data.status=='error'){
    		switch(data.errordescription){
    			case "incomplete_installation":
    				hideLoadingDialog();
    				getIncompleteInstallationForm(data.payload);
    			break;
    			
    			case "not_logged_in":
    				hideLoadingDialog();
    				getLoginDialog(data.payload);
    			break;
    		}
    	}
		else{
			hideLoadingDialog();
		}
    });
};
function getLoginDialog(data){
	$("#dialog_login").remove();
	
	$('<dialog class="mdl-dialog" id="dialog_login"><h4 class="mdl-dialog__title">G+ Login required!</h4><div class="mdl-dialog__content"><p>Please log-in with your g+ account by clicking</p><a href="'+decodeURIComponent(data.login_url)+'">this log-in link</a><p>or log in as<br><a href="'+apiurl+'?code=guest">guest</a></p></div>').appendTo("body");
	var dialog = document.querySelector('#dialog_login');
    if (! dialog.showModal) {
      dialogPolyfill.registerDialog(dialog);
    }
    componentHandler.upgradeDom();
    dialog.showModal();
}


function getIncompleteInstallationForm(data){
	//create the form dialog
	//remove if existing
	$("#dialog_installform").remove();
	$('<dialog class="mdl-dialog" id="dialog_installform"><h4 class="mdl-dialog__title">Install Assistant</h4><div class="mdl-dialog__content"></div><div class="mdl-dialog__actions"><button id="btn_sendInstall" type="button" class="mdl-button">Set Credentials</button><button id="btnInstallAbort" type="button" class="mdl-button close">Finish Installation</button></div></dialog>').appendTo("body");
	$("#btnInstallAbort").css("display","none");
	var content=$("#dialog_installform").find(".mdl-dialog__content").first();
	content.append($("<p>It seems that your server is not set up completely. Please provide the missing data.</p>"));
	var output="<form action='#' method='POST'>";
	$.each(data,function(index,value){
		switch(value){
			case "googleApi":
				installLibs=false;
				content.append('<button id="b_download_api" class="mdl-button mdl-js-button mdl-button--raised mdl-button--colored">Install Google API</button>');
				content.append('<div id="disp_spinner"><span id="install_g_api">Installing Google PHP Api on Server </span><div id="install_g_api_spinner" class="mdl-spinner mdl-js-spinner is-active"></div></div>');
				$("#disp_spinner").css("display","none");
				$("#b_download_api").click(function(evt){
					$(this).fadeOut();
					$("#disp_spinner").fadeIn();
					$.post(apiurl,"{\"c\":\"installLibs\"}",function(data){
						if(data.status=='error'){
							installLibs=false;
							$("<div id=\"d_lib_status\" class=\"icon material-icons\">error outline</div><div class=\"mdl-tooltip\" for=\"d_lib_status\">"+data.errordescription+"</div>").insertAfter("#disp_spinner");
						}
						else{
							installLibs=true;
							$("<div id=\"d_lib_status\" class=\"icon material-icons\">done</div><div class=\"mdl-tooltip\" for=\"d_lib_status\">Installation successful</div>").insertAfter("#disp_spinner");
						}
						$("#disp_spinner").remove();
						$("#b_download_api").remove();
						componentHandler.upgradeDom();
						proceedInstallation();
					},"json");
				});
			break;
			case "g_api_usage":
				output+="<label class=\"mdl-checkbox mdl-js-checkbox mdl-js-ripple-effect\" for=\"uses_g_api\">";
				output+="<input type=\"checkbox\" name=\"uses_g_api\" id=\"uses_g_api\" class=\"mdl-checkbox__input\">";
				output+="<span class=\"mdl-checkbox__label\">Use Google+ User Auth</span>";
				output+="</label>";
			break;
			case "configfile":
				//don't do anything
			break;
			default:
				setcredentials=false;
				//default are Input files
				output+="<div class=\"mdl-textfield mdl-js-textfield mdl-textfield--floating-label\">";
				output+="<input class=\"mdl-textfield__input\" type=\"text\" name=\""+value+"\" id=\""+value+"\">";
				output+="<label class=\"mdl-textfield__label\" for=\""+value+"\">"+value+"</label>";
				output+="</div>";
			break;
		}
	});
	output+="</form>";
	content.append(output);
	
	var dialog = document.querySelector('#dialog_installform');
    if (! dialog.showModal) {
      dialogPolyfill.registerDialog(dialog);
    }
    
    componentHandler.upgradeDom();
    
    $("#btn_sendInstall").click(function(){
    	 var data = $("#dialog_installform form").serialize().split("&");
    	    var obj={};
    	    for(var key in data)
    	    {
    	        obj[data[key].split("=")[0]] = data[key].split("=")[1].trim();
    	    }
    	    //set checkbox
    	    obj["uses_g_api"]=($("#uses_g_api").is(":checked"))?"yes":"no";
    	    
    	    var request={c:"setCredentials",payload:obj};
    	    var request_string=JSON.stringify(request);
    	    
    	    $("#dialog_installform").find("form").css("display","none");
    	    var content=$("#dialog_installform").find(".mdl-dialog__content").first();
    	    content.append("<div id=\"credential_spinner\"><span>Setting credentials</span><div class=\"mdl-spinner mdl-js-spinner is-active\"></div></div>");
    	    $("#btn_sendInstall").css("display","none");
    	    componentHandler.upgradeDom();
    	    $.post(apiurl,request_string,function(data){
    	    	if(data.status=="error"){
    	    		setcredentials=false;
    	    		$("<div id=\"d_lib_status\" class=\"icon material-icons\">error outline</div><div class=\"mdl-tooltip\" for=\"d_lib_status\">"+data.errordescription+"</div>").insertAfter("#credential_spinner");
    	    	}
    	    	else{
    	    		setcredentials=true;
    	    		$("<div id=\"d_lib_status\" class=\"icon material-icons\">done</div><div class=\"mdl-tooltip\" for=\"d_lib_status\">Installation successful</div>").insertAfter("#credential_spinner");
    	    	}
    	    	$("#credential_spinner").remove();
    	    	componentHandler.upgradeDom();
    	    	proceedInstallation();
    	    },"json");
    });
    $("#btnInstallAbort").click(function(){
    	document.querySelector("#dialog_installform").close();
    	runInit();
    });
    
    dialog.showModal();
}

function proceedInstallation(){
	if(setcredentials&&installLibs){
		$("#btnInstallAbort").css("display","block");
	}
	else{
		$("#btnInstallAbort").html("restart Installation").css("display","block");
	}
}