<table>
	<tr><th>contact name</th><th>phone</th><th>extension</th><th>type</th><th>actions</th>
	{foreach from=$data item=eachRecord}
		{ts}
		<tr id="phone-{$eachRecord.phone_id}" class="crm-entity {$eachRecord.phone_id}">
		<td><a title="Edit {$eachRecord.display_name}'s contact record." href="/civicrm/contact/add?reset=1&action=update&cid={$eachRecord.contact_id}">{$eachRecord.display_name}</a></td>
		<td><span id="phone-{$eachRecord.phone_id}" class="crmf-phone crm-editable">{$eachRecord.phone}</span></td>
		<td><span id="phone-{$eachRecord.phone_id}" class="crmf-phone-ext crm-editable">{$eachRecord.phone_ext}</span></td>
		<td>
			<select class="select_setPhoneType" phone_id="{$eachRecord.phone_id}" selectedValue="{$eachRecord.phone_type}">
				{crmAPI var="OptionValueS" entity="OptionValue" action="get" sequential="1" option_group_name="phone_type" option_sort="weight"}
				{foreach from=$OptionValueS.values item=OptionValue}
					<option value="{$OptionValue.value}">{$OptionValue.label}</option>
				{/foreach}
			</select>
		</td>
		<td>

			<a title="Edit {$eachRecord.display_name}'s contact record." href="{crmURL p='civicrm/contact/add' q='reset=1&action=update&cid='}{$eachRecord.contact_id}">edit contact</a> | 
			<a title="Remove this phone number forever from the contact's record. Doesn't touch the rest of the contact's details!" class="button_delete" href="#" phone_id="{$eachRecord.phone_id}">delete phone</a> | 
			<a title="Hide this phone number from view for now." class="button_hide" href="#" phone_id="{$eachRecord.phone_id}">hide</a>
		</td>{/ts}</tr>
	{/foreach}
</table>
