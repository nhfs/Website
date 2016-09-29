function returnObjById( id )
{
    if (document.getElementById)
        var returnVar = document.getElementById(id);
    else if (document.all)
        var returnVar = document.all[id];
    else if (document.layers)
        var returnVar = document.layers[id];
    return returnVar;
}

/*  make hover colors, drop-downs work */
startList = function() {
	if (document.all) {
	navRoot = returnObjById("nav");
	for (i=0; i<navRoot.childNodes.length; i++) {
		node = navRoot.childNodes[i];
		if (node.nodeName=="LI") {
			node.onmouseover=function() {
				this.className+=" over";
			}
  			node.onmouseout=function() {
			  this.className=this.className.replace(" over", "");
		   }
   		}
	}}
}
window.onload=startList;


/*function drop(id) {
    node=returnObjById(id);
	node.style.display='block';
}

function shrink(id) {
    node=returnObjById(id);
	node.style.display='none';
}*/
