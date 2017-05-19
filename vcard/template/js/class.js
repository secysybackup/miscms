// JavaScript Document
var d = document,
	dw = d.documentElement.clientWidth,
	dh = d.documentElement.clientHeight;
d.addEventListener("DOMContentLoaded", function(){
	var catalog = d.querySelector(".catalog");
	catalog.style.height = d.body.scrollHeight+"px";
	catalog.style.webkitTransform = "translateX("+dw+"px)";
	catalog.getElementsByTagName("ul")[0].style.height = d.body.scrollHeight+"px";
	//catalog.webkitTransform = "translateX("+dw+"px)";
	catalog.style.display = "block";
	d.querySelector(".catbtn").addEventListener("click", function(){
		catalog.style.webkitTransform = "translateX("+dw+"px)";	
	});
	d.querySelector("#catlist").addEventListener("click", function(){
		catalog.style.webkitTransform = "translateX(0px)";
	});
});


function showSubCatalog(obj) {
	var subcatalog = d.querySelectorAll(".subcatalog");
	if(subcatalog.length) {
		for(var i=0; i<subcatalog.length; i++) {
			subcatalog[i].style.display = "none";
			subcatalog[i].previousSibling.previousSibling.style.backgroundImage = "url(../statics/images/arrow2.png)";
		}
		obj.nextSibling.nextSibling.style.display = "block";
		obj.style.backgroundImage = "url(../statics/images/arrow3.png)";
	}
}
