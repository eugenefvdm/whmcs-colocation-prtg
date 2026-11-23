<h3>Network Traffic</h3>

{if $prtgGraphConfigured}
<img src="{$prtgGraphBase}?serviceid={$serviceId}&graphid=0" alt="2 hours network traffic graph" />

<img src="{$prtgGraphBase}?serviceid={$serviceId}&graphid=1" alt="2 days network traffic graph" />

<img src="{$prtgGraphBase}?serviceid={$serviceId}&graphid=2" alt="30 days network traffic graph" />

<img src="{$prtgGraphBase}?serviceid={$serviceId}&graphid=3" alt="365 days network traffic graph" />
{else}
<p class="text-muted">No network graph is configured for this service.</p>
{/if}
