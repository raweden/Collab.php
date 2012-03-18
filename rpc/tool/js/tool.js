
$(document).ready(function(){
	
	
	$(window).on("resize",doResize);
	doResize();
	
	// Initializes the 
	$("#commands").on("click", function(event){
		if(!event.isDefaultPrevented()){
			casadeController.hide();
			event.preventDefault();
			event.stopPropagation();	
		}
	});
	
	$("#argumentPanel").on("click", function(event){
		if(!event.isDefaultPrevented()){
			casadeController.hide();
			event.preventDefault();
			event.stopPropagation();	
		}
	});
	
	$("#resultView").on("click", function(event){
		if(!event.isDefaultPrevented()){
			casadeController.show();
			event.preventDefault();
			event.stopPropagation();	
		}
	});
	
	$("#tool-headers").on("click",function(){
		tool.showModalView();
	});
	
	$("#overlay-close").on("click",function(){
		tool.hideModalView();
	});
	
	// Determines the most logic URL to connect to.
	
	var loc = window.location;
	var url = "http://localhost/rpc/";
	if(loc.protocol == "http:",loc.protocol == "https:"){
		var parts = loc.href.split("/");
		var url = [];
		for(var i = 0;i<parts.lenght;i++){
			var part = parts[i];
			console.log(part);
			url.push(part);
			if(part == "rpc"){
				break;
			}
		}
		url = url.join("/");
	}
	
	tool.url = url;
	tool.init();
	
	console.log(url);
	
});

function doResize(){
	var h = window.innerHeight;
	var w = window.innerWidth;
	
	var th = $("#toolbar").innerHeight();

	$("#arguments").height(h-th);
}

if (!Function.prototype.bind){
	Function.prototype.bind = function(scope) {
	  var _function = this;

	  return function() {
	    return _function.apply(scope, arguments);
	  }
	}
}

(function() {

    function listener(event) {
        var child = event.relatedTarget;
        var ancestor = event.target;
        // cancel if the relatedTarget is a child of the target
        while (child) {
            if (child.parentNode == ancestor) return;
            child = child.parentNode;
        }

        // dispatch for the child and each parentNode except the common ancestor
        ancestor = event.target.parentNode;
        var ancestors = [];
        while (ancestor) {
            ancestors.push(ancestor);
            ancestor = ancestor.parentNode;
        }
        ancestor = event.relatedTarget;
        while (ancestor) {
            if (ancestors.indexOf(ancestor) != -1) break;
            ancestor = ancestor.parentNode;
        }
        child = event.target;
        while (child) {
            var mouseEvent = document.createEvent('MouseEvents');
            mouseEvent.initEvent(event.type.replace('mouse', 'roll'),
                    false, // does not bubble
                    event.cancelable,
                    event.view,
                    event.detail, event.screenX, event.screenY,
                    event.ctrlKey, event.altKey, event.metaKey, event.button,
                    event.relatedTarget);
            child.dispatchEvent(mouseEvent);
            child = child.parentNode;
            if (child == ancestor) break;
        }
    }

    // setup the rollover/out events for components to use
    document.addEventListener('mouseover', listener, false);
    document.addEventListener('mouseout', listener, false);
})();

function Tool(){
	
	var _result = "";
	
	var _url = null;
	
	this.headers = {};
	
	this.commands = null;
	
	this.command = null;	// holds the name of the current selected command.
	
	this.args = [];
	
	this._sendButton = null;
	
	this.selectedIndex = null;
	
	this.init = function(){
		
		this.call("collab.commands", null, function(message){
			
			if(message.contentType != "application/json"){
				console.log(message.text);
			}
			
			var commands = message.data;
			console.log(message.data);
			this.commands = commands;
			
			var list = window.document.getElementById("commands");
			
			for(var name in commands){
								
				var command = commands[name];
				
				// generating the command outline.
				var args = command.arguments;
				var outline = [];
				// appending each argument of the command for getting the method outline.
				for(var i = 0;i<args.length;i++){
					outline.push(args[i].name);
				}
				// generating outline string.
				outline = name + "(" + outline.join(", ") + ")";
				command.outline = outline;
				
				// creating list cell.
				var cell = new TableViewCell();
				cell.text = outline;
				cell.id = name;
				
				var details = command.description;
				var dot = details.indexOf(".");
				if(dot != -1){
					details = details.substr(0,dot+1);
				}
				
				cell.details = details;
				
				cell.on("click", function(cell){
					
					// 
					if(this.selectedIndex !== null){
						this.selectedIndex.selected = false;
					}
					this.selectedIndex = cell;
					cell.selected = true;
					
					//
					this.showDetails(cell.id);
					
					//console.log("cell clicked "+cell.id);
				}.bind(this));
				
				list.appendChild(cell.element);
				//link.setAttribute('href', 'mypage.htm');
			}
						
		}.bind(this));
		
		//
		var button = window.document.getElementById("tool-send");
		button = new UIButton(button);
		utils.unselectable(button.element);
		
		button.callback = function(){
			
			if(this.command == null){
				return;
			}
			
			var cells = this.args;
			
			var arguments = [];
			
			//
			for(var i = 0;i<cells.length;i++){
				var cell = cells[i];
				var value = cell.value;
				
				if(utils.isInteger(value) === true){
					value = parseInt(value);
				}else if(utils.isFloat(value) === true){
					value = parseFloat(value);
				}else if(value === "true"){
					value = true;
				}else if(value === "false"){
					value = false;
				}else{
				
					try{
						var obj = JSON.parse(value);
						if(obj){
							value = obj; 	
						}
					}catch(e){
						
					}
				}
				
				arguments.push(value);
				
			}
			
			console.log(arguments);
			
			tool.appendMessage("Sending Message to "+this.command,null);
			
			// Executes the command.
			
			this.call(this.command,arguments,function(message){
				tool.addItem(message);
			}.bind(this)).time = Date.now();
			
			
		}.bind(this);
		this._sendButton = button;
		
		// 
		var button = window.document.getElementById("tool-headers");
		button = new UIButton(button);
		utils.unselectable(button.element);
		
		button.callback = function(){
			console.log("should display popup to manage headers");
			tool.showModalView();
		}.bind(this);		
		
		window.casadeController = new Cascade(document.getElementById("argumentPanel"),
			document.getElementById("resultView"),[320,640],120);
	}
	
	/**
	 * 
	 * 
	 */
	this.showDetails = function(name){
				
		var list = window.document.getElementById("arguments");

		
		var command = this.commands[name];		
		var arguments = command.arguments;
		
		this.command = name;

		if( list.hasChildNodes() ){
			
			while ( list.childNodes.length >= 1 ){
				list.removeChild(list.firstChild);
		    }
		}
		
		this.args = [];
		
		var header = new HeaderCell();

		var isHtml = new RegExp("<.*?>","g");
		if(isHtml.test(command.description) == true){
			$(command.description).appendTo(header.element);
		}else{
			header.text = command.description;
		}
		
		list.appendChild(header.element);
		
		if(arguments.length == 0){
			
			// adding separator cell before message cell.
			var cell = new SeparatorCell();
			cell.text = "No Arguments";
			list.appendChild(cell.element);
			
		}else{
			// adding separator cell before message cell.
			var cell = new SeparatorCell();
			cell.text = "Arguments";
			list.appendChild(cell.element);
			
			var details = this.commands[name].details;
		
			// adding message cells (argument list).
			for(var i = 0;i<arguments.length;i++){
				var arg = arguments[i];
				var cell = new MessageCell();
				cell.id = arg.name;
				cell.text = arg.name;
				cell.details = arg.details;
				
				if(arg.hasOwnProperty("default") ){
					if(arg.default === null){
						cell.placeholder = "null";
					}else{
						cell.value = arg.default;
					}
				}else{
					// no default value provided, required!
					cell.placeholder = "value";
				}
				this.args.push(cell);
				list.appendChild(cell.element);
			}
		}
		
		/* Inserts a return group after the argument group.
		if(command.hasOwnProperty("return") && command["return"] != null){
			// adding separator cell before message cell.
			var cell = new SeparatorCell();
			cell.text = "Returns";
			list.appendChild(cell.element);
			//
			var note = new HeaderCell();
			note.text = command["return"];
			$(note.element).css("height","auto");

			list.appendChild(note.element);
		}
		*/
	}
	
	Object.defineProperty(this,"url",{
		set: function(value){
			if(typeof value === "string"){
				_url = value;
			}
		},
		get: function(){
			return _url;
		}
	});
	
	Object.defineProperty(this,"result",{
		set: function(value){
			
			var output = window.document.getElementById("output");

			var len = value.len;
			value = utils.beautify(value);
			output.textContent = value;
			_result = value;
		},
		get: function(){
			return _result;
		}
	});
	
	/**
	 * 
	 * 
	 * @param name
	 * @param value
	 */
	this.setRequestHeader = function(name,value){
		this.headers[name] = value;
	}
		
	/**
	 * 
	 * 
	 * @param command A String representing the command name.
	 * @param arguments A Array of arguments to be provided to the command.
	 * @param callback A Function to be called when data is retrived.
	 */
	this.call = function(command,arguments,callback){
		
		var message = new RemoteMessage(command,arguments,callback);
		// sets the defined headers.
		var headers = this.headers
		for(var name in headers){
			message.setRequestHeader(name,headers[name]);
		}
		// sends the message.
		message.send(this.url);
		return message;
	}
}


tool = new Tool();

tool.setRequestHeader("auth_token","d334cd0263576d63ba448719e5cd180808b54edc");

tool.appendMessage = function(title,result){
	
	var message = $("<li></li>");
		
	if(title){
		var header = $("<p></p>")
		/*
		if(result){
			var expand = $("<a class=\"message-expand\" style=\"float:left;\">+</a>");
			expand.on("click",function(){
				console.log("expand button where clicked");
			})
			expand.get(0).onselectstart = function(){ return false };
			expand.appendTo(header)
			
			var copy = $("<a class=\"message-copy\" style=\"float:right;\">copy</a>");
			copy.on("click",function(){
				console.log("copy button where clicked");
			})
			copy.get(0).onselectstart = function(){ return false };
			copy.appendTo(header)
		}
		*/
		
		$("<span style=\"float:center;\">"+title+"</span>").appendTo(header);
		
		header.appendTo(message);
	}
	
	if(result){
		$("<pre><code>" + result + "</code></pre>").appendTo(message)
	}
	
	message.appendTo("#collab-result");
	message.css("display","none");
	message.fadeIn(300);
	
	$("#resultView").animate({ scrollTop: $("#resultView").attr("scrollHeight") }, 300);
}

var itemIndex = 0;

tool.previewItem = function(index){
	$("#overlay").fadeIn(300);
}

/**
 * 
 * @param message {RemoteMessage} 
 */
tool.addItem = function(message){
	
	var item = $("<li></li>");
	var type = message.contentType;
	var data = message.text;
	
	// adding title to item.
	var time = Date.now() - message.time;
	var title = data.length + " bytes resived ("+ type +") "+ time + " ms";
	$("<p><span style=\"float:center;\">"+title+"</span><a class=\"tool-ql\" onclick=\"tool.previewItem("+itemIndex+")\"></a></p>").appendTo(item);
	if(type == "application/json"){
		
		data = utils.beautify(data);
		$("<pre><code>" + data + "</code></pre>").appendTo(item)
		
	}else if(type == "image/jpeg" || type == "image/png"){
		// data:image/png;base64,
		var image = new Image();//;charset=binary
		image.src = "data:" + type + ";base64," + data;
		$(image).appendTo(item);
	}else if(type == "text/html"){
		$("<pre><code>" + data + "</code></pre>").appendTo(item)
	}
	
	item.appendTo("#collab-result");
	item.css("display","none");
	item.fadeIn(300);
	
	$("#resultView").animate({ scrollTop: $("#resultView").attr("scrollHeight") }, 300);
	itemIndex++;
}

tool.showModalView = function(){
	
	$("#overlay").fadeIn(300);
}

tool.hideModalView = function(){
	
	$("#overlay").fadeOut(300);
}

function Cascade(child1,child2,expanded,spacing){
	
	this._child1 = child1;
	this._child2 = child2;
	this._expanded = expanded;
	this._spacing = spacing;
	
	child1.style.position = "absolute";
	child1.style.left = this._expanded[0] + "px";
	
	child2.style.position = "absolute";
	child2.style.left = expanded[1] + "px";
	child2.style.width = window.innerWidth-(spacing*2) + "px";
	/*
	child2.addEventListener("rollover", function(event){
		this.show();
	}.bind(this));
	
	child2.addEventListener("rollout", function(event){
		this.hide();
	}.bind(this));
	*/
	this.show = function(){
		var child1 = this._child1;
		var child2 = this._child2;
		
		$(child1).clearQueue();
		$(child1).animate({
			left: this._spacing
		}, 600, function() {
			// Animation complete.
		});
		
		$(child2).clearQueue();
		$(child2).animate({
			left: this._spacing*2
		}, 600, function() {
			// Animation complete.
		});
		
	}
	
	this.hide = function(){
		
		var child1 = this._child1;
		var child2 = this._child2;
		
		$(child1).clearQueue();
		$(child1).animate({
			left: this._expanded[0]
		}, 600, function() {
			// Animation complete.
		});
		
		$(child2).clearQueue();
		$(child2).animate({
			left: this._expanded[1]
		}, 600, function() {
			// Animation complete.
		});
		
	}
	
	$(window).on("resize",function(){
		$("#resultView").width(window.innerWidth+(spacing*2));
		$("#resultView").height(window.innerHeight);
	})
		
}


// Base Classes


function Responder(){

	this._listeners = {};
	this._trigger = [];

	/**
	 * Adds a listener to the end of the listeners array for the specified event.
	 */
	this.addListener = function(event, callback){
		this.on(event, callback);
	}

	/**
	 * Adds a listener to the end of the listeners array for the specified event.
	 */
	this.on = function(event, callback){
		if(typeof event !== "string" || typeof callback != "function"){
			throw new Error("adding listener failed! invalid type of arguments");
		}

		var index = -1;
		if (!this._listeners.hasOwnProperty(event)){
			this._listeners[event] = new Array();
		}else{
			index = this._listeners[event].indexOf(callback);
		}
		// Refenrences the callback under the type if not already registerd.
		if(index === -1){
			this._listeners[event].push(callback);
		}
	}

	/**
	* Adds a one time listener for the event. 
	* The listener is invoked only the first time the event is fired, after which it is removed.
	*/
	this.once = function(event, callback){
		this.on(event,callback);
		// References the callback under trigger once array if not already registerd.
		var index = this._trigger.indexOf(callback);
		if(index === -1){
			this._trigger.push(callback);
		}
	}

	/**
	 * Remove a listener from the listener array for the specified event.
	 */
	this.removeListener = function(event, callback){
		if(typeof event !== "string" || typeof callback != "function"){
			throw new Error("remove listener failed! invalid of type arguments");
		}
		// Removes the listener if it exists under reference of the event type.
		var listeners = this._listeners[type];
		var index = listeners.indexOf(callback);
		if(index != -1){
			listeners.splice(index,1);
		}
		// Removes the listeners array for the type if empty.
		if(listeners.length === 0){
			listeners[type] = null;
		}

		// Removes the listner from once array if it exists there.
		listeners = this._trigger;
		index = listeners.indexOf(callback);
		if(index != -1){
			listeners.splice(index,1);
		}
	}

	/**
	 * Removes all listeners from the listener array for the specified event.
	 */
	this.removeAllListeners = function(event){
		if(this._listeners.hasOwnProperty(event) === true){
		 	this._listeners[event] = null;
		}
	},

	/**
	 * Returns an array of listeners for the specified event.
	 * Manipulating the retuned Array dont affect the listeners in the EventEmitter.
	 */
	this.listeners = function(event){
		return this._listeners.hasOwnProperty(event) ? this._listeners[event].concat() : null;
	}

	/**
	 * Dispatches a Event to all listeners with the supplied arguments.
	 * 
	 * Arguments after the event argument can be provided and these arugments are also sent to the listener.
	 * 
	 * @param event {String} The event identifier type.
	 */
	this.emit = function(event){

		// Determinening if any listener exist on the current event.
		if(event !== undefined  && event !== null && this._listeners.hasOwnProperty(event) === true){

			// Copying the addiditonal arguments sent to this method.
			var args = [];
			for(var i = 0;i<arguments.length;i++){
				args.push(arguments[i]);
			}
			args.shift();

			// Executes all listener methods.
			var responders = this._listeners[event];
			var len = responders.length;
			for(var i = 0;i<len;i++){
				var callback = responders[i];
				if(typeof callback !== "function"){
					continue;
				}
				callback.apply(null,args);
				// Determine if the callback where referenced as a once listener, in that case it's reference is removed.
				var index = this._trigger.indexOf(callback);
				if(index != -1){
					removeListener(event,callback);
				}
			}
		}
	}

}


// User Interfaces Constructors.


function TableViewCell(type){
	
	// calling super constructor.
	Responder.call(this);
	
	var _type = type || "li";
	
	var _text = null;
	var _details = null;
	var _id = null;
	
	// Main Container.
	
	var _element = document.createElement(_type);

	_element.setAttribute("class","table-cell");
	_element.setAttribute("id",name);
	
	_element.onclick = function(event){
		this.emit("click",this);
	}.bind(this);
	
	utils.unselectable(_element);
	
	// Text Container.
	
	var _labelView = document.createElement("span");
	_labelView.setAttribute("class","cell-title");
	_element.appendChild(_labelView);
	utils.unselectable(_labelView);
	
	var _detailsView = null;
	
	// Setting and Getting Selected.
	
	var _selected = false;
	
	Object.defineProperty(this,"selected",{
		set: function(value){
			if(typeof value === "boolean" && value !== _selected){
				if(value){
					$(_element).css("background-color","rgba(255,255,255,0.03)");
					//_element.setAttribute("class","tableViewCell command command-selected label-fix");
				}else{
					$(_element).css("background-color","");
					//_element.setAttribute("class","tableViewCell command command-normal label-fix");
				}
				_selected = value;
			}
			console.log("cell.. is now " + (value ? "selected" : "non-selected"));
		},
		get: function(){
			return _selected;
		}, configurable: true});
	
	// Setting and Getting Label and Detailed Text.
	
	Object.defineProperty(this,"text",{
		set: function(value){
			_labelView.textContent = value
			_text = value;
		},
		get: function(){
			return _text;
		}, configurable: true});
	
	Object.defineProperty(this,"details",{
		set: function(value){
			this.detailsView.textContent = value
			_details = value;
		},
		get: function(){
			return _details;
		}, configurable: true});
	
	Object.defineProperty(this,"id",{
		set: function(value){
			_id = value;
		},
		get: function(){
			return _id;
		}, configurable: true});
		
	// Getting Elements
		
	Object.defineProperty(this,"element",{
		get: function(){
			return _element;
		}, configurable: false});
		
	Object.defineProperty(this,"labelView",{
		get: function(){
			return _labelView;
		}, configurable: false});
		
	Object.defineProperty(this,"detailsView",{
		get: function(){
			if(_detailsView === null){
				_detailsView = document.createElement("span");
				_detailsView.setAttribute("class","cell-details");
				_element.appendChild(_detailsView);
				utils.unselectable(_detailsView);
			}
			return _detailsView;
		}, configurable: false});
	
	
}


function MessageCell(){
	
	// Calling Super Constructor.
	TableViewCell.call(this,"li");
	
	// Overriding element properties.
	this.element.setAttribute("class","table-cell message-cell");
	
	this.labelView.setAttribute("class","param-title");
	this.detailsView.setAttribute("class","cell-details params-details");

	// Input element.
	
	var _input = document.createElement("textarea");
	_input.setAttribute("class","param-input");
	this.element.appendChild(_input);
	
	function autoResize(){
		var cols = _input.cols;
		var a = _input.value.split("\n");
		var b = 1;
		for (var x = 0;x < a.length; x++){
			if (a[x].length >= _input.cols)
				b += Math.floor(a[x].length/_input.cols);
		}
		b+= a.length;
		if (b > _input.rows) _input.rows = b;
	}
	
	_input.onkeyup = autoResize.bind(this);
	
	// Getting input values
	
	Object.defineProperty(this,"placeholder",{
		set: function(value){
			_input.placeholder = value;
		},
		get: function(){
			return _input.placeholder;
		}
	});
	
	Object.defineProperty(this,"value",{
		set: function(value){
			_input.value = value;
		},
		get: function(){
			return _input.value;
		}
	});
	
}


console.log(0x2d);

function HeaderCell(){
	
	// Calling Super Constructor.
	TableViewCell.call(this,"li");
	
	// Applying custom style to element.
	this.element.setAttribute("class","table-cell header-cell");
	this.labelView.setAttribute("class","cell-title header-text");	
	
}


function SeparatorCell(){
	
	// Calling Super Constructor.
	TableViewCell.call(this,"li");
	
	// applying custom style to element.
	this.element.setAttribute("class","table-cell table-separator");
	
}


function UIButton(element){
	
	var _element = element;
	
	
	$(_element).on("click", function(event){
		if(!event.isDefaultPrevented() && this.callback){
			this.callback(this);
			event.preventDefault();
		}
	}.bind(this));
	
	Object.defineProperty(this,"element",{
		get: function(){
			return _element;
		}
	});
	
}


// Remoting call wrapper.


/**
 * 
 * 
 * @param command A String representing the command name.
 * @param arguments A Array of arguments to be provided to the command.
 * @param callback A Function to be called when data is retrived.
 */
function RemoteMessage(command,arguments,callback){
	
	var _command = command;
	var _arguments = arguments;
	
	command = command.split(".");
	
	var _body = {};
	_body.methodName = command.pop();
	_body.serviceName = command.join(".");
	_body.parameters = arguments;
	_body = JSON.stringify(_body);
		
	var _request = null;
	var _callback = typeof callback == "function" ? callback : null; 
	
	var _text = null;
	
	var _data = null;
	
	var _headers = {"Content-Type":"application/json"};
	
	var _contentType = null;
	
	/**
	 * 
	 * 
	 * @param name
	 * @param value
	 */
	this.setRequestHeader = function(name,value){
		_headers[name] = value;
	}
	
	/**
	 * 
	 * 
	 * @param url
	 */
	this.send = function(url){
		_request = new XMLHttpRequest();
		_request.open("POST", url, true);
		
		for(var name in _headers){
			var value = _headers[name];
			_request.setRequestHeader(name,value);
		}
		
		_request.onreadystatechange = function(oEvent){
			if(_request.readyState === 4) {
				
				if(_request.status === 200){
					
					_text = _request.responseText;
					_contentType = _request.getResponseHeader("Content-Type");
					_callback(this);
				}else{
					_callback(this);
				}				
		  }
		}.bind(this);
		
		_request.send(_body);
	}
	
	/**
	 * 
	 * 
	 */
	Object.defineProperty(this,"command",{
		get: function(){
			return command;
		}
	});
	
	/**
	 * 
	 * 
	 */
	Object.defineProperty(this,"contentType",{
		get: function(){
			return _contentType;
		}
	});
		
	/**
	 * 
	 * 
	 */
	Object.defineProperty(this,"data",{
		get: function(){
			if(_data == null && _text != null){
				_data = JSON.parse(_text);
			}
			return _data;
		}
	});
	
	/**
	 * 
	 * 
	 */
	Object.defineProperty(this,"text",{
		get: function(){
			return _text;
		}
	});
	
}


// Utility methods


utils = {};

/**
 * Pretty formating a JSON object string.
 * 
 * @param string A Json string.
 * @author Raweden.
 */
utils.beautify = function(string){
	
	var tab = "    ";
	var result = "";
	var indent = 0; 
	var inString = false; 
	
	// replaces json escaped charaters.
	string = string.replace("\\/","/",string);
	var len = string.length;

	for(var i = 0; i < len; i++){ 
		var char = string.charAt(i);
		switch(char) { 
			case "{": 
			case "[": 
				if(!inString){ 
					result += (char + "\n" + repeat(tab, indent+1));
					indent++; 
				}else{ 
					result += char; 
				} 
				break; 
			case "}": 
			case "]": 
				if(!inString){ 
					indent--; 
					result += "\n" + repeat(tab, indent) + char; 
				}else{ 
					result += char; 
				} 
				break; 
			case ",": 
				if(!inString){ 
					result += ",\n" + repeat(tab, indent); 
				}else{ 
					result += char; 
				} 
				break; 
			case ":": 
				if(!inString){ 
					result += ":"; 
				}else{ 
					result += char; 
				} 
				break; 
			case "\"": 
				if(i > 0 && string.charAt(i-1) !== "\\"){ 
					inString = !inString; 
				} 
			default: 
				result += char; 
				break;					
		} 
	}
	
	function repeat(char,len){
		var str = "";
		for(var i = 0;i<len;i++){
			str += char;
		}
		return str;
	}
	
	// returns the pretty printed JSON string.
	return result;
}


utils.unselectable = function(element){
	element.onselectstart = function(){ return false };
}


utils.isInteger = function(str){
	
	var int_regexp = /^\s*(\+|-)?\d+\s*$/;
	return str.search (int_regexp) != -1
}


utils.isFloat = function(str){
	
	var reg = new RegExp("^[-]?[0-9]+[\.]?[0-9]+$");
	 return reg.test(str);
}