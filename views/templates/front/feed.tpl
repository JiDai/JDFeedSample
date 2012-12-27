
{capture name=path}{$title}{/capture}
	{include file="$tpl_dir./breadcrumb.tpl"}
	{include file="$tpl_dir./errors.tpl"}

<h1>{$title}</h1>

<div>
	{if $feed}
		<ul>
			{foreach from=$feed item='item'}
			<li><b>{$item->title}</b><br/>
				{$item->description|nl2br}
			</li>
			{/foreach}
		</ul>
	{/if}
</div>
