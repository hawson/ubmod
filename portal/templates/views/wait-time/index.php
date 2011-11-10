<script type="text/javascript">
Ext.onReady(function() {
    Ubmod.app.addPartial({
        element: 'dash-chart',
        url: '/wait-time/chart'
    });
});
</script>
<div id="dash-chart"></div>
<div class="chart-desc">
Plot of the average wait time for a job to begin running versus the job size
(number of processors). Note, this data is skewed by the users fairshare utilization, 
meaning that heavy users experience longer wait times than average users. Plots of average
wait times are also available.
</div>
