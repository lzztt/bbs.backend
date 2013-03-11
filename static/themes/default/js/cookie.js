function setCookie(name, value, expires, path, domain, secure) {
  var curDomain = '.' + document.domain.split('.').slice(-2).join('.');
  var curCookie = name + "=" + escape(value) + "; expires=" + expires.toUTCString() +
  ((path) ? "; path=" + path : "; path=/") +
  ((domain) ? "; domain=" + domain : "; domain=" + curDomain) +
  ((secure) ? "; secure" : "");
  document.cookie = curCookie;
}
/*
function getCookie(name) {
  var curCookie = document.cookie.split("; ");
  for (var i=0; i < curCookie.length; i++) {
    var crumb = curCookie[i].split("=");
    if (name == crumb[0])
      return unescape(crumb[1]);
  }

  return null;
}
*/

// This function retrieves a cookie
function getCookie(name) {
  var dc = document.cookie;
  var cname = name + "=";

  if (dc.length > 0) {
    begin = dc.indexOf(cname);
    if (begin != -1) {
      begin += cname.length;
      end = dc.indexOf(";", begin);
      if (end == -1) end = dc.length;
      return unescape(dc.substring(begin, end));
    }
  }
  return null;
}


function deleteCookie(name) {
  var expDate = new Date();
  expDate.setHours( parseInt(expDate.getHours()) - 12 );
  document.cookie = name + "=; expires="+expDate.toUTCString()+";";
}
