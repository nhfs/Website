// preload images
var imgs = new Array()
imgs[0]=new Image();
imgs[0].src="images/welcome_backi.gif";
imgs[1]=new Image();
imgs[1].src="images/welcome_backa.gif";
imgs[2]=new Image();
imgs[2].src="images/featured_backi.gif";
imgs[3]=new Image();
imgs[3].src="images/featured_backa.gif";
imgs[4]=new Image();
imgs[4].src="images/books_backi.gif";
imgs[5]=new Image();
imgs[5].src="images/books_backa.gif";
imgs[6]=new Image();
imgs[6].src="images/tools_backi.gif";
imgs[7]=new Image();
imgs[7].src="images/tools_backa.gif";
imgs[8]=new Image();
imgs[8].src="images/clothing_backi.gif";
imgs[9]=new Image();
imgs[9].src="images/clothing_backa.gif";
imgs[10]=new Image();
imgs[10].src="images/cards_backi.gif";
imgs[11]=new Image();
imgs[11].src="images/cards_backa.gif";
imgs[12]=new Image();
imgs[12].src="images/more_backi.gif";
imgs[13]=new Image();
imgs[13].src="images/more_backa.gif";

function swap (obj, id) {
	obj.src=imgs[id].src;
}

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

function drop(id) {
    node=returnObjById(id);
	node.style.display='block';
}

function shrink(id) {
    node=returnObjById(id);
	node.style.display='none';
}
