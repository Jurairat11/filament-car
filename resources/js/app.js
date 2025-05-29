import './bootstrap';

import Alpine from 'alpinejs';

import Chart from 'chart.js/auto';
import ChartDataLabels from 'chartjs-plugin-datalabels';

Chart.register(ChartDataLabels);

window.Alpine = Alpine;

Alpine.start();
