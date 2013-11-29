/*
 ### Zeitguys Item Ratings Plugin v1.0 ###
*/
jQuery(document).ready(function( $ ){
	
	//Init vars
	var ajaxRequestUrl 		= ZgItemRatingsVars.ajaxUrl;
	var nonceValue			= ZgItemRatingsVars.ajaxNonce;
	var rateUpdateAction	= ZgItemRatingsVars.rateUpdateAction;
	
	$('.zg-item-ratings-rateit').rateit({ max: 5 });
	
	$('.zg-item-ratings-rateit').bind('rated reset', function (e) {

		var ri = $(this);
		
		//Cache rate item vars
		var value 			= ri.rateit('value');
		var itemID 			= ri.data('itemid'); 
		var ratingGroupID	= ri.data('ratinggroupid');
		
		//maybe we want to disable voting?
		ri.rateit('readonly', true);
		
		//Make ajax request to update item rating
		$.ajax({
			url: ajaxRequestUrl,
			data: { 
				action: rateUpdateAction,
				zgItemRateNonce: nonceValue,
				itemID: itemID, 
				rateValue: value,
				ratingGroup: ratingGroupID
			},
			type: 'POST',
			success: function (data) {
			
			},
			error: function (jxhr, msg, err) {
			
			}
		});
		
		
	});
	
});