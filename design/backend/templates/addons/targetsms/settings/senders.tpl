{assign var="data" value=""|fn_get_senders_data}

{foreach from=$data.companies item=company}
	<div class="control-group">
		<label for="company_sender_{$company.company_id}" class="control-label">{$company.company}:</label>
		<div class="controls">
			<select class="user-success" name="sender_data[{$company.company_id}][sms_sender_name]" id="company_sender_{$company.company_id}">
				{foreach from=$data.senders item=sender}
					<option {if $sender == $company.sms_sender_name}selected{/if}>{$sender}</option>
				{/foreach}
			</select>
		</div>
	</div>
{/foreach}