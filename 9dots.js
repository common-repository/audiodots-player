var target = document.getElementById('nine-dots-player');
const config = {
    attributes: true,
    childList: true,
    subtree: true
}; 
const callback = function(mutationsList, observer) {
    for (let mutation of mutationsList) {
		if (mutation.type === 'childList') {
			var elem = document.getElementsByClassName('ninedots-player')
			if(elem.length > 0){
				var height = 'height:0px;';
				document.getElementById("collapseOne").setAttribute("style", height);
				document.getElementById("collapseSpeed").setAttribute("style", height);
				document.getElementById("collapseShare").setAttribute("style", height);
				stopObsever(observer);
			} 
        } 
    }
};
const observer = new MutationObserver(callback);
observer.observe(target, config);	
function stopObsever(observer){
	observer.disconnect();
}