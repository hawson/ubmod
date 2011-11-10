<div style="padding: 10px;">
<?php if ($queue): ?>
<div style="padding-top: 5px;" class="labelHeading">
Queue: <span class="labelHeader"><?php echo $queue['queue'] ?></span> &nbsp;&nbsp;
</div>
<div id="queue-container">
<div id="queue-form" style="width: 600px;"></div>
</div>

<div style="padding: 5px;margin-bottom: 20px;margin-top:10px;">
<table class="dtable">
<tr>
	<th>Users: </th>
	<td style="font-weight: bold"><?php echo $queue['user_count'] ?></td>
	<th>Total Jobs: </th>
	<td style="font-weight: bold"><?php echo number_format($queue['jobs']) ?></td>
	<th>Avg. Wall (d): </th>
	<td style="font-weight: bold"><?php echo $queue['avg_wallt'] ?></td>
	<th>Avg. Wait (h): </th>
	<td style="font-weight: bold"><?php echo $queue['avg_wait'] ?></td>
</tr>
<tr>
	<th>Avg. MEM: </th>
	<td style="font-weight: bold"><?php echo number_format($queue['avg_mem']) ?></td>
	<th>Avg. Job Size (CPUs): </th>
	<td style="font-weight: bold"><?php echo $queue['avg_cpus'] ?></td>
	<th>Avg. Job Size (Nodes): </th>
	<td style="font-weight: bold"><?php echo $queue['avg_nodes'] ?></td>
	<th>Avg. Exec (h): </th>
	<td style="font-weight: bold"><?php echo $queue['avg_exect'] ?></td>
</tr>
</table>
<input type="hidden" id="qid" value="<?php echo $queue['queue_id'] ?>"/>
</div>
<?php else: ?>
No job data found for queue in given time period.
<?php endif; ?>
</div>
