/**
 * @author Arsen Leontijevic
 */
var domElement = function(selector) {
 this.selector = selector || null;
 this.element = null;
};
/**
 * @todo Init object with one or more elements if they exists in dom
 */
domElement.prototype.init = function() {
 this.element = document.querySelector(this.selector);
};
domElement.prototype.on = function(event, callback) {
 var evt = this.eventHandler.bindEvent(event, callback, this.element);
}
domElement.prototype.off = function(event) {
 var evt = this.eventHandler.unbindEvent(event, this.element);
}
domElement.prototype.val = function(newVal) {
 return (newVal !== undefined ? this.element.value = newVal : this.element.value);
};
domElement.prototype.append = function(html) {
 this.element.innerHTML = this.element.innerHTML + html;
 return this;
};
domElement.prototype.prepend = function(html) {
 this.element.innerHTML = html + this.element.innerHTML;
 return this;
};
domElement.prototype.html = function(html) {
	 if (html === undefined) {
	 return this.element.innerHTML;
	 }
	 this.element.innerHTML = html;
	 return this;
};
domElement.prototype.all = function() {
	    var items = {},
	    results = [],
	    length = 0,
	    i = 0;
	    // this doesn't work on IE 8- and Blackberry Browser
	    results = Array.prototype.slice.call(document.querySelectorAll(this.selector));
	    length = results.length;
	    // add the results to the items object
	    for ( ; i < length; ) {
	      items[i] = results[i];
	      i++;
	    }
	    // add some additional properties to this items object to 
	    // make it look like an array
	    items.length = length;
	    items.splice = [].splice();
	    // add an 'each' method to the items
	    items.each = function(callback) {
	      var i = 0;
	      for ( ; i < length; ) {
	        callback.call(items[i]);
	        i++;
	      }
	    }
	    return items;
};
domElement.prototype.eventHandler = {
 events: [],
 bindEvent: function(event, callback, targetElement) {
 this.unbindEvent(event, targetElement);
 targetElement.addEventListener(event, callback, false);
 this.events.push({
 type: event,
 event: callback,
 target: targetElement
 });
 },
 findEvent: function(event) {
 return this.events.filter(function(evt) {
 return (evt.type === event);
 }, event)[0];
 },
 unbindEvent: function(event, targetElement) {
 var foundEvent = this.findEvent(event);
 if (foundEvent !== undefined) {
 targetElement.removeEventListener(event, foundEvent.event, false);
 }
 this.events = this.events.filter(function(evt) {
 return (evt.type !== event);
 }, event);
 }
};
sei = function(selector) {
 var el = new domElement(selector);
 el.init();
 return el;
}