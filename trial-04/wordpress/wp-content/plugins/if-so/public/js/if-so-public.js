
(function( $ ) {
	'use strict';

	$(document).ready(function () {

		function sendAjaxReq(action, data, cb) {
			data['action'] = action;
			data['nonce'] = nonce;
			data['page_id'] = ifso_page_id;

			$.post(ajaxurl, data, function(response) {
				if (cb)
					cb(response);
			});
		}
		sendAjaxReq('ifso_add_page_visit', {}, function(response){
			var data = JSON.parse(response);
			console.log(data);
		});

	});

})( jQuery );

