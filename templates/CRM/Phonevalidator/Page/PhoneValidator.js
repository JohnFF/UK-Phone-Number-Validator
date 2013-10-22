{literal}
<script type="text/javascript" language="javascript">

	// set each of the select functions to have its correct initial value
        cj.each(cj('select'), function(){
		cj(this).val(cj(this).attr("selectedValue"));
        });   

	// change the page when a new phone type to show is selected
	cj('#showPhoneType').change(function() {
		var old_url = window.location.pathname;
		var old_url_without_params = old_url.substring(0, old_url.indexOf('?'));
		var new_url = old_url_without_params + "?phone_type=" + cj('#showPhoneType').attr("value");
		window.location.href = new_url;
	});

	// add the functionality to save the new phone type if it's changed 
        cj('.select_setPhoneType').change(function() {
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
