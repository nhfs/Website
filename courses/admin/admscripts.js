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


function toggle (id) {
	var obj = returnObjById(id);
	if (obj.style.display == 'none') {
		obj.style.display = 'block';
	} else {
		obj.style.display= 'none';
	}
}
