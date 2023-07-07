var $ = jQuery.noConflict();
$(document).ready(function() {
	var count = price.count;
    $(".addPrice").click(function(){
        count = count + 1;
        $('#priceField').append('<p><input type="text" name="price['+count+'][variable]" value="" placeholder="type"/>  <input type="number" name="price['+count+'][amount]" value="" placeholder="amount"/><btn class="btn btn-primary remove">Remove</btn></p>');
        return false;
    })
    $(document).on('click', '.remove', function() {
        $(this).parent().remove();
    });
});