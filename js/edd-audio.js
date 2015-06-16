var edd_vars;
jQuery(document).ready(function($) {
	$(function() {	
		$("#edd_preview_files_wrap table").sortable({
			handle: '.edd_ap_drag_handle', items: '.edd_repeatable_upload_wrapper', opacity: 0.6, cursor: 'move', axis: 'y', update: function() {
				var $this = $(this);
				var field = $this.closest('tr');
				var meta_id = $('input.ecpt_repeatable_upload_field_name', field).attr('id');
				var inputs = '';
				$('#edd_preview_files_wrap input').each(function() {
					var $this =  $(this);
					inputs = inputs + $this.attr('name') + '=' + $this.val() + '&';
				});

				var order = inputs + 'action=edd_update_audio_files_order&post_id=' + edd_vars.post_id + '&nonce=' + edd_ap_vars.nonce;
				$.post(ajaxurl, order, function(theResponse){
					// show response here, if needed
					//alert(theResponse);
				});
			}
		});
	})
});