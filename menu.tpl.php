<h2>Device Login Settings</h2>
<form class='dspdl-device-login-settings' data-target="<?php echo admin_url('admin-ajax.php'); ?>">
	<table class='widefat'>
		<thead>
			<tr>
				<th colspan=2><h3>API Settings</h3></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td class='save-message'></td>
			</tr>
			<tr>
				<td><label for='dspdl-device-login-api-key'>API Key</label></td>
				<td><input type='text' name='dspdl-device-login-api-key' placeholder='1651ABCD56456' value='<?php echo get_option("dspdl_dsp_api_key"); ?>'/></td>
			</tr>
		</tbody>
		<tfoot>
			<td colspan=2>
				<button class="button button-primary">Save</button>
			</td>
		</tfoot>
	</table>
</form>