 require(['jquery', 'jquery/ui'], function($){
	 $( document ).ready(function() {
                        $(".qty_increment").click(function(){
                var curr_qty = parseInt($(this).parent().parent().find(".input-text.qty").val());
                $(this).parent().parent().find('.input-text.qty').val((curr_qty*1)+1);
                });
                $(".qty_decrement").click(function(){
                var curr_qty = parseInt($(this).parent().parent().find(".input-text.qty").val());
                if(curr_qty > 1){
                $(this).parent().parent().find('.input-text.qty').val((curr_qty*1)-1);
                }
                });
      });
 });
