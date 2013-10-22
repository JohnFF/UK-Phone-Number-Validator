{include file="CRM/common/crmeditable.tpl"}
<h3>Broken Phone Numbers</h3>
Show <select id="showPhoneType" selectedValue="{$selected_show_phone_type}">
				<option value="no_type_selected">all phone types</option>
                                {crmAPI var="OptionValueS" entity="OptionValue" action="get" sequential="1" option_group_name="phone_type" option_sort="weight"}
                                {foreach from=$OptionValueS.values item=OptionValue}
                                        <option value="{$OptionValue.value}">{$OptionValue.label}</option>
                                {/foreach}
                        </select>
<p>This section shows the first {$broken_count} of {$broken_total} contacts that have a {$selected_show_phone_type_label} number that looks to be broken. It either doesn't start with a 0 ({$broken_count_nozero}), doesn't have 11 digits excluding spaces ({$broken_count_noteleven}), or contains a non-numerical character ({$broken_count_containsnonnumber}).</p>

{if $broken_count eq 0}
	<p>There appear to be no broken {$selected_show_phone_type_label} numbers.</p> 
{else}
	{include file="CRM/Phonevalidator/Page/PhoneTable.tpl" data=$broken_output}
{/if}

<br/>

<h3>Mobile Numbers as Landlines</h3>
<p>This section shows the first {$mob_in_ll_count} of {$mob_in_ll_total} contacts that have an entry for a landline that looks like a mobile phone. If the number is valid then it should be changed to be a mobile phone entry.</p>

{if $mob_in_ll_count eq 0}
	<p>There appear to be no valid landline numbers labelled as mobile numbers.</p> 
{else}
	{include file="CRM/Phonevalidator/Page/PhoneTable.tpl" data=$mob_in_ll_output}
{/if}

<br/>

<h3>Landline Numbers as Mobiles</h3>
<p>This section shows the first {$ll_in_mob_count} of {$ll_in_mob_total} contacts that have an entry for a mobile phone that looks like a landline. If the number is valid then it should be changed to be a landline entry.</p>

{if $ll_in_mob_count eq 0}
	<p>There appear to be no valid mobile numbers labelled as landline numbers.</p> 
{else}
	{include file="CRM/Phonevalidator/Page/PhoneTable.tpl" data=$ll_in_mob_output}
{/if}

<!--
<h3>Broken Numbers by Source</h3>
{if $src_grp_count eq 0}
	<p>There appear to be no invalid numbers.</p> 
{else}
	{include file="CRM/Phonevalidator/Page/PhoneTable.tpl"}
{/if}
-->

{include file="CRM/Phonevalidator/Page/PhoneValidator.js"}
