window.addEventListener('load', startsynchr);
window.addEventListener('error', catcherr);

if (document.all) {
    document.onmouseover = linkover;
    document.onmouseout = linkout;
    document.onmouseup = linkover;
    document.ondragstart = linkover;
    document.onclick = linkover;
}

function linkover() {
    var obj = event.srcElement, s = status;
    if (obj.tagName == "A") s = obj.innerText; else if (obj.tagName == "IMG") s = obj.title;
    status = s;
    return true;
    return true;
}
function linkout() {
    status = defaultStatus;
    return true;
    return true;
}

function fmtint(i) {
    return (i < 10 ? "0" + i : i);
}

var serverFormatter = new Intl.DateTimeFormat('de-DE', {
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit',
    hour12: false,
    timeZone: stz
});

function synchrclock() {
    var now = new Date().getTime();
    var serverTime = (stm * 1000) + (now - sltm);
    getLay("server-time").innerHTML = serverFormatter.format(serverTime) + " Uhr";
}
function startsynchr() {
    setInterval(synchrclock, 1000);
}


function param(name) {
    var s, a, x, i;
    s = location.search;
    if (s == "" || s.indexOf(name + "=") == -1) {
        if (!(top.frames.location == null)) s = top.frames.location.search;
    }
    if (s != "" && s.indexOf(name + "=") > -1) {
        s = s.substring(1);
        a = new Array();
        x = new Array();
        x = s.split("&");
        for (i = 0; i < x.length; i++) {
            a[i] = new Array();
            a[i] = x[i].split("=");
        }
        for (i = 0; i < a.length; i++) {
            if (a[i][0].toLowerCase() == name.toLowerCase()) break;
        }
        return unescape(a[i][1]);
    } else {
        return "";
    }
}


let newwin;
function show_abook(type) {

    l = parseInt((screen.availWidth - 500) / 2);
    t = parseInt((screen.availHeight - 300) / 2);

    p = "width=500,height=300,toolbar=0,menubar=0,location=0,status=0,resizable=1,scrollbars=1,left=" + String(l) + ",top=" + String(t);
    newwin = window.open("abook.php?mode=selpage&sid=" + param('sid') + "&type=" + type, "AddressBook", p);
    setTimeout('newwin.focus();', 200);
}


function setCaret(obj) {
    if (obj.createTextRange) {
        obj.caretPos = document.selection.createRange().duplicate();
    }
}

function smily(str) {
    var txtbox = document.formular.text;

    if (txtbox.createTextRange && txtbox.caretPos) {
        txtbox.caretPos.text = " " + str + " ";
    } else {
        txtbox.value += " " + str + " ";
    }

    txtbox.focus(txtbox.caretPos);
}


function getLay(id) {
    if (document.getElementById) return document.getElementById(id);
    if (document.all) return eval("document.all." + id);
    return 0;
}

function insertads(file, ifh, ifw) {
    if (file.substr(0, 4) != "http") file = "info/" + file;
    document.write('<iframe src="' + file + '" width="' + ifw + '" height="' + ifh + '" scrolling="no" marginheight="0" marginwidth="0" frameborder="0"></iframe>');
}

function catcherr() {
    return true;
}