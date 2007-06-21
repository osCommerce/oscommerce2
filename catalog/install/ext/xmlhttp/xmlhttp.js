/*
  $Id: $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2005 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

var http;

function loadXMLDoc(url, functionCall) {
  http = getHTTPObject();

  if (http) {
    http.onreadystatechange = functionCall;
    http.open("GET", url, true);
    http.send("");
  }
}

function getHTTPObject() {
  var xmlhttp;

/*@cc_on
  @if (@_jscript_version >= 5)
    try {
      xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
    } catch (e) {
      try {
        xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
      } catch (E) {
        xmlhttp = false;
      }
    }
  @else
    xmlhttp = false;
@end @*/

  if (!xmlhttp && typeof XMLHttpRequest != 'undefined') {
    try {
      xmlhttp = new XMLHttpRequest();
    } catch (e) {
      xmlhttp = false;
    }
  }

  return xmlhttp;
}

function getKeyCode(evt) {
  if (evt) {
    return evt.keyCode;
  }

  if (window.event) {
    return window.event.keyCode;
  }
}

function getEventSource(evt) {
  if (evt) {
    return evt.target;
  }

  if (window.event) {
    return window.event.srcElement;
  }
}

function cancelEvent(evt) {
  if (evt) {
    evt.preventDefault();
    evt.stopPropagation();
  }

  if (window.event) {
    window.event.returnValue = false;
  }
}

function hideDiv(obj) {
  if (obj.style.visibility == 'visible') {
    obj.style.visibility = 'hidden';
    obj.style.display = 'none';
  }
}

function showDiv(obj) {
  if (obj.style.visibility != 'visible') {
    obj.style.visibility = 'visible';
    obj.style.display = 'inline';
  }
}

function isDivVisible(obj) {
  return (obj.style.visibility == 'visible');
}

function urlEncode(string) {
  if (window.encodeURIComponent) {
    return encodeURIComponent(string);
  }

  return escape(string);
}

function urlDecode(string) {
  if (window.decodURIComponent) {
    return decodeURIComponent(string);
  }

  return unescape(string);
}
