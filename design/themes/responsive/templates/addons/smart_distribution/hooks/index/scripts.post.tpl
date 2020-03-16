{if $smarty.request.click}
<script>
	$(document).ready(function () {
		$('{$smarty.request.click}').click();
	});
</script>
{/if}