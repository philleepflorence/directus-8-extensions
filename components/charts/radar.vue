<script>
	import { Radar } from 'vue-chartjs';

	export default {
		extends: Radar,
		props: [
			'chartdata', 
			'options'
		],
		mounted () {
			this.renderChart(this.chartdata, this.options);
		}
	}
</script>