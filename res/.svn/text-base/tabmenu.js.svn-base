
var prevTab = 0;
function hasClass(ele,cls) {
	if (ele)
		return ele.className.match(new RegExp('(\\s|^)'+cls+'(\\s|$)'));
	else 
		alert('error unknown class='+ele)
}
function addClass(ele,cls) {
	if ((ele != null) && !this.hasClass(ele,cls)) 
		ele.className += " "+cls;
}
function removeClass(ele,cls) {
	if (hasClass(ele,cls)) {
		var reg = new RegExp('(\\s|^)'+cls+'(\\s|$)');
		ele.className=ele.className.replace(reg,' ');
	}
}

function selectTab(which) {
	which = parseInt(which);
	curTabContent = document.getElementById('tabcontent-'+which);
	curTab = document.getElementById('tab-'+which);
	if (curTabContent) {
		if (prevTab) {
			oldTabContent = document.getElementById('tabcontent-'+prevTab);
			oldTab = document.getElementById('tab-'+prevTab);
			removeClass(oldTab,'active');
			removeClass(oldTabContent,'active');
		}
		prevTab = which;
		addClass(curTab,'active');
		addClass(curTabContent,'active');
		
		updateArrows(which);
	}
}
function advanceTab(dir) {
	dir = parseInt(dir);
	if (prevTab) {
		nextTab = prevTab + dir;
	}
	else 
		nextTab = 1 + dir;
	selectTab(nextTab);
	return false;
}

function updateArrows(curTab) {
	curTab = parseInt(curTab);
	prevBtn = document.getElementById('goPrevSection');
	if (prevBtn) {
		
		if (!document.getElementById('tab-'+(curTab-1))) 
			addClass(prevBtn,'hide');
		else
			removeClass(prevBtn,'hide');
	}
	nextBtn = document.getElementById('goNextSection');
	if (nextBtn) {
		if (!document.getElementById('tab-'+(curTab+1))) 
			addClass(nextBtn,'hide');
		else
			removeClass(nextBtn,'hide');
	}	
}