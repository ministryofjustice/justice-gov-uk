var expDays = 30;
var exp = new Date();
exp.setTime(exp.getTime() + (expDays*24*60*60*1000));

export function SetCookie (name, value) {
  var argv = SetCookie.arguments;
  var argc = SetCookie.arguments.length;
  var expires = (argc > 2) ? argv[2] : null;
  var path = (argc > 3) ? argv[3] : null;
  var domain = (argc > 4) ? argv[4] : null;
  var secure = (argc > 5) ? argv[5] : false;

  document.cookie = name + "=" + escape (value) +
    ((expires == null) ? "" : ("; expires=" + expires.toGMTString())) +
    ((path == null) ? "" : ("; path=" + path)) +
    ((domain == null) ? "" : ("; domain=" + domain)) +
    ((secure == true) ? "; secure" : "");
}

export function DeleteCookie (name) {
  var exp = new Date();
  exp.setTime (exp.getTime() - 1);
  var cval = GetCookie (name);
  document.cookie = name + "=" + cval + "; expires=" + exp.toGMTString();
}

export function getCookieVal (offset) {
  var endstr = document.cookie.indexOf (";", offset);
  if (endstr == -1)
  endstr = document.cookie.length;
  return unescape(document.cookie.substring(offset, endstr));
}

export function GetCookie (name) {
  var arg = name + "=";
  var alen = arg.length;
  var clen = document.cookie.length;
  var i = 0;
  while (i < clen) {
    var j = i + alen;
    if (document.cookie.substring(i, j) == arg)
      return getCookieVal (j);
    i = document.cookie.indexOf(" ", i) + 1;
    if (i == 0) break;
  }
  return null;
}

export function SetCookieConsent (name,value,days) {
  if (days) {
		  var date = new Date();
		  date.setTime(date.getTime()+(days*24*60*60*1000));
		  var expires = ";expires="+date.toGMTString();
  } else 
		  expires = "";
		  document.cookie = name+"="+value+expires+"; path=/";
}
