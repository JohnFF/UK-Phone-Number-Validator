{include file="CRM/common/crmeditable.tpl"}
<h3>Broken Phone Numbers</h3>
<p>This section shows the first {$broken_count} of {$broken_total} contacts that have a phone number that looks to be broken. It either has a space in it ({$broken_count_space}), or doesn't start with a 0 ({$broken_count_nozero}), doesn't have 11 letters ({$broken_count_noteleven}), or contains a '(' character ({$broken_count_containsbracket}).</p>

{if $broken_count eq 0}
	<p>There appear to be no broken phone numbers.</p> 
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
