$(document).ready(function(){
	$("#sub_deals option[value='Commercial']").hide();
	//Loading Progress Start
	$("#myDiv").hide();
	$("#loading-image").hide();
	//Loading Progress End

	$(".onlyNumbers").keypress(function (e) {
     	if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
            return false;
    	}
   	});

	$('#clear_btn').on('click', function(){
		$('#mortgage_form')[0].reset();
		$('.sourcePreviewXml').html('');
		$(".sourcexml_values").show()
		$('.mortgage_values'). hide();
	});

	$('.mortgage_values'). hide();
	$('#previewXml').on('click', function(){
	    if($(".mortgage_values").is(":visible")){
			$(".sourcexml_values").show()
			$('.mortgage_values'). hide();
		}           				
	});

	$('#mortgage_type').on('change', function(){
		$("#sub_deals").prop("disabled",false);
		$('#sub_deals').get(0).selectedIndex = 0;
		$('input[name="type"]').val('Residential');
		$('input[name="purpose"]').val('Purchase');
		$("#repayment__").prop("checked",true);
		if(($(this).val() == 'first_time_buyer') || ($(this).val() == 'moving_home')){
			$("#sub_deals option[value='Commercial']").hide();
			$("#sub_deals option[value!='Commercial']").show();
		}else if($(this).val() == 'buy_to_let'){
			$("#interest-only__").prop("checked",true);
			$('input[name="type"]').val('buy-to-let');
			$("#sub_deals option[value!='Commercial']").hide();
			$("#sub_deals option[value='Commercial']").show();
		}else if($(this).val() == 'remortgage'){
			$('input[name="purpose"]').val('Remortgage');
			$("#sub_deals").prop("disabled",true);
		}
	});

	
	$('#calculate_btn').on('click', function(){

		var delay = 2000;
        $("#myDiv").show();
        $("#loading-image").show();
		//Condition to check if the mortage amount greater than property value
		var property_value = $('#property_value').val();
		var mortgage_amount = $('#mortgage_amount').val();
		var mortgage_terms = $('#terms').val();
		var deals_fixed = $.trim($("input[name='deals_fixed']:checked"). val());
		var deals_variable = $.trim($("input[name='deals_variable']:checked"). val());		
		if(property_value == ''){
			alert('Enter the Property Value');
			$('#property_value').focus();
			$("#myDiv").hide();
        	$("#loading-image").hide();
			return false;
		}else if(mortgage_amount == ''){
			alert('Enter the Mortgage Amount');
			$('#mortgage_amount').focus();
			$("#myDiv").hide();
        	$("#loading-image").hide();
			return false;
		}else if(parseInt(mortgage_amount) > parseInt(property_value)){
			alert('Mortgage Amount should be less than Property Value');
			$('#mortgage_amount').focus();
			$("#myDiv").hide();
        	$("#loading-image").hide();
			return false;
		}else if(mortgage_terms == ''){
			alert('Enter the Mortgage Terms');
			$('#terms').focus();
			$("#myDiv").hide();
        	$("#loading-image").hide();
			return false;
		}else if((deals_fixed == '') && (deals_variable == '')) {
			alert("Please Choose any option in 'Type of Mortgage'");
			$('#deals_fixed').focus();
			$("#myDiv").hide();
        	$("#loading-image").hide();
			return  false;
		}
		$.ajax({
            type:'POST',
            url: 'preview_xml.php',
            data: $('#mortgage_form').serialize(),
            headers: {'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')},
            success:function(data){
            	$("#myDiv").hide();
        		$("#loading-image").hide();
                $('.sourcePreviewXml').html(data);
                if($(".mortgage_values").is(":visible")){
					$(".sourcexml_values").show()
					$('.mortgage_values'). hide();
				}
            }
        })
		
	});

	$('#resultsDiv').on('click', function(){
		var delay = 2000;
        $("#myDiv").show();
        $("#loading-image").show();
		//Condition to check if the mortage amount greater than property value
		var property_value = $('#property_value').val();
		var mortgage_amount = $('#mortgage_amount').val();
		var mortgage_terms = $('#terms').val();
		var deals_fixed = $.trim($("input[name='deals_fixed']:checked"). val());
		var deals_variable = $.trim($("input[name='deals_variable']:checked"). val());		
		if(property_value == ''){
			alert('Enter the Property Value');
			$('#property_value').focus();
			$("#myDiv").hide();
        	$("#loading-image").hide();
			return false;
		}else if(mortgage_amount == ''){
			alert('Enter the Mortgage Amount');
			$('#mortgage_amount').focus();
			$("#myDiv").hide();
        	$("#loading-image").hide();
			return false;
		}else if(parseInt(mortgage_amount) > parseInt(property_value)){
			alert('Mortgage Amount should be less than Property Value');
			$('#mortgage_amount').focus();
			$("#myDiv").hide();
        	$("#loading-image").hide();
			return false;
		}else if(mortgage_terms == ''){
			alert('Enter the Mortgage Terms');
			$('#terms').focus();
			$("#myDiv").hide();
        	$("#loading-image").hide();
			return false;
		}else if((deals_fixed == '') && (deals_variable == '')) {
			alert("Please Choose any option in 'Type of Mortgage'");
			$('#deals_fixed').focus();
			$("#myDiv").hide();
        	$("#loading-image").hide();
			return  false;
		}
		$.ajax({
            type:'POST',
            url: 'calculate.php',
            data: $('#mortgage_form').serialize(),
            headers: {'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')},
            success:function(data){
            	$("#myDiv").hide();
        		$("#loading-image").hide();
                $('.mortgage_values').html(data);
                if($(".sourcexml_values").is(":visible")){
					$('.mortgage_values'). show();
					$(".sourcexml_values").hide()
				}	
            }
        })
			
	});

	
});


