
<!-- Block feed -->
<div id="feed_list" class="block">
	<h4>{$title}</h4>
	<div class="block_content">
		<ul class="">
		{foreach from=$feed item=feedItem}
			<li>- {$feedItem->title}</li>
		{/foreach}
		</ul>
		<a href="{$link->getModuleLink('JDFeedSample', 'feed')}">&raquo; {l s='Show all'}</a>
	</div>
</div>
<!-- /Block feed -->
