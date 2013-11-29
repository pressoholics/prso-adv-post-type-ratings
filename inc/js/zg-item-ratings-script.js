/*
 ### Zeitguys Item Ratings Plugin v1.0 ###
*/
jQuery(document).ready(function( $ ){
	
	$('.zg-item-ratings-rateit').rateit({ max: 5 });
	
	$('.zg-item-ratings-rateit').bind('rated reset', function (e) {

		var ri = $(this);
		
		//if the use pressed reset, it will get value: 0 (to be compatible with the HTML range control), we could check if e.type == 'reset', and then set the value to  null .
		var value = ri.rateit('value');
		var productID = ri.data('productid'); // if the product id was in some hidden field: ri.closest('li').find('input[name="productid"]').val()
		
		//maybe we want to disable voting?
		ri.rateit('readonly', true);
		
		/*
		$.ajax({
			url: 'rateit.aspx', //your server side script
			data: { id: productID, value: value }, //our data
			type: 'POST',
			success: function (data) {
			 $('#response').append('<li>' + data + '</li>');
			
			},
			error: function (jxhr, msg, err) {
			 $('#response').append('<li style="color:red">' + msg + '</li>');
			}
		});
		*/
		
	});
	
});