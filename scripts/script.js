/*
 * This is a custom script file that is loaded for each instance on each page.
 *
 * Use to it add whatever client scripts your project might need
 */

(function() {
   /*
	   Force dark mode for all users...
   */
   
   document.body.classList.add('dark');
   
   /*
	   Custom Window Events
   */
   
   let isScrolling;
   let scrollEnd = new Event('scrollend');
   
   window.addEventListener('scroll', function ( event ) {

		/*
			Clear our timeout throughout the scroll
		*/
		
		window.clearTimeout( isScrolling );
	
		/*
			Set a timeout to run after scrolling ends
		*/ 
		
		isScrolling = setTimeout(function () {
	
			document.dispatchEvent(scrollEnd);
	
		}, 300);
	
	}, false);
})();