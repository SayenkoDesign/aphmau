var error = null;
var data = null;
var total_data_to_send = 0;
var max_row_chunk = 50; //50
var current_row_chunk = 0;
var last_row_chunk = 0;
	
jQuery(document).ready(function()
{
	jQuery('#star-import-button').on('click',wcst_start_upload_csv);
	jQuery('#csv_file').on('change',wcst_process_csv_file);
	jQuery('#import-again-button').on('click',wcst_reload_page);
});
function wcst_reload_page(event)
{
	location.reload(true);
}
function wcst_process_csv_file(evt) 
	{
		if (!wcst_browserSupportFileUpload()) {
        alert('The File APIs are not fully supported in this browser!');
        } else {
            data = null;
            var file = evt.target.files[0];
            var reader = new FileReader();
            reader.readAsText(file);
            reader.onload = function(event) {
                var csvData = event.target.result;
				try{
                data = jQuery.csv.toArrays(csvData);
				}catch(e){error = e;}
                if (data && data.length > 0) {
                  //alert('Imported -' + data.length + '- rows successfully!');
				  total_data_to_send = data.length;
				  //console.log();
                } else {
                    alert('No data to import! Error: '+error);
                }
            };
            reader.onerror = function() {
                alert('Unable to read ' + file.fileName);
            };
        }
    }
	
function wcst_start_upload_csv(e)
{
	if(e != null)
	{
		e.preventDefault();
		e.stopImmediatePropagation();
		
		last_row_chunk = 1;
		current_row_chunk = max_row_chunk;
	}
	if(error !=null || data == null)
	{
		 alert('File is not valid');
		return false;
	}
	
	
	var dataToSend =  [];
	dataToSend.push(data[0]);
	for(var i = last_row_chunk;  i < current_row_chunk; i++)
	{
		//console.log("Row: "+i);
		dataToSend.push(data[i]);
	}
	
	//UI
	wcst_importing_data_transition_in();
	
	setTimeout(function(){wcst_upload_csv(dataToSend)}, 1000);;
}
function wcst_upload_csv(dataToSend)
{
	var formData = new FormData();
	formData.append('action', 'wcst_upload_tracking_csv');  
	formData.append('csv', dataToSend.join("<#>")); 
	var perc_num = ((current_row_chunk/total_data_to_send)*100);
	perc_num = perc_num > 100 ? 100:perc_num;
	
	var perc = Math.floor(perc_num);
	jQuery('#ajax-progress').html("<p>computing data, please wait...<strong>"+perc+"% done</strong></p>");
	//UI
	wcst_set_progress_bar_level(perc);
				
	jQuery.ajax({
		url: ajaxurl, //defined in php
		type: 'POST',
		data: formData,//{action: 'upload_csv', csv: data_to_send},
		async: false,
		success: function (data) {
			//alert(data);
			wcst_check_response(data);
		},
		error: function (data) {
			//alert("error: "+data);
			wcst_check_response(data);
		},
		cache: false,
		contentType: false,
		processData: false
	});
		
}
function wcst_browserSupportFileUpload() 
{
	var isCompatible = false;
	if (window.File && window.FileReader && window.FileList && window.Blob) {
	isCompatible = true;
	}
	return isCompatible;
}
function wcst_check_response(data)
{
	//UI
	wcst_appent_status_text(data);
	if(current_row_chunk < total_data_to_send)
		{
			last_row_chunk = current_row_chunk;
			current_row_chunk += max_row_chunk;
			if(current_row_chunk > total_data_to_send)
				current_row_chunk = total_data_to_send;
			
			wcst_start_upload_csv(null);
		}
		else
		{
			wcst_set_progress_bar_level(100);
			wcst_importing_data_transition_out();
		}
}