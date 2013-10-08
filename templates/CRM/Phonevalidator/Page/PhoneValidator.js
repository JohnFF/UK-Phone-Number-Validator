{literal}
<script type="text/javascript" language="javascript">

	// set each of the select functions to have the correct initial value
        cj.each(cj('select'), function(){
                if (cj(this).attr("selected") != 1){
                        cj(this).val(cj(this).attr("selectedValue"));
                }
        });   

	// add the functionality to save the new phone type if it's changed 
        cj('select').change(function() {
                var phone_id = cj(this).attr("phone_id");
                var new_value = cj(this).attr("value");        
                cj().crmAPI ('Phone','update',{ id:phone_id, phone_type_id:new_value }
			,{ success:function (data){
			}
		});     
		return false;
	});

        // make the delete phone record links work
        cj('.button_delete').click(function(){
                var phone_id = cj(this).attr('phone_id');
                cj().crmAPI ('Phone','delete',{ id:phone_id }
                        ,{ success:function (data){    
                                cj("."+phone_id).fadeOut(); 
			}       
                });     
                return false;
        });     

        // make the hide phone record links work
        cj('.button_hide').click(function(){
                var phone_id = cj(this).attr('phone_id');
                cj(this).parent().parent().fadeOut(); 
                return false;
        });     

</script>
{/literal}
