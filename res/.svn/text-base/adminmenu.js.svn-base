function showReport(report) {
	reportInit();
	$('reportLightbox').update(report);
	showBox();
}

function showBox() {
	boxWidth =750;
	boxHeight = 500;

	// set max and min width and height
	setContentSize($('reportLightbox'),boxWidth, boxHeight, 600, 200);

	// center window
//	screenWidth =document.all ? document.body.clientWidth  : window.innerWidth;
//	screenHeight=document.all ? document.body.clientHeight : window.innerHeight;
//	xPos = (screenWidth - boxWidth) * 0.5;
//	yPos = (screenHeight - boxHeight) * 0.5;
//	$('reportLightbox').setStyle({
//		left: xPos + 'px',
//		top:  yPos + 'px'});

	//S how the background overlay and lightbox...
	$('screenOverlay').show();
	$('reportLightbox').show();
}

function closeBox() {
	// Hide the overlay and tobox...
	$('screenOverlay').hide();
	$('reportLightbox').hide();
}

/* Set the max and min width and height */
function setContentSize(obj, maxWidth, maxHeight, minWidth, minHeight) {
	windowHeight = document.viewport.getHeight();
	objHeight = obj.getHeight() + 100;
	if (objHeight > windowHeight) objHeight = windowHeight;
	if (objHeight > maxHeight) objHeight = maxHeight;

	windowWidth = document.viewport.getWidth();
	objWidth = obj.getWidth() + 10;
	if (objWidth > (windowWidth * 2)) objWidth = windowWidth * 2;
	if (objWidth > maxWidth) objWidth = maxWidth;

	obj.setStyle({
		maxHeight: objHeight + 'px',
		maxWidth: objWidth + 'px',
		minWidth: minWidth + 'px',
		minHeight:minHeight + 'px'
	});
}

function reportInit() {
	// setup overlay and lightbox
	overlayElement = new Element(
		'div',
		{'id':'screenOverlay', 'style': 'display: none;'}
	).update(' ');

	body = $(document.getElementsByTagName('body')[0]);
	body.insert({'top':overlayElement});	
}
