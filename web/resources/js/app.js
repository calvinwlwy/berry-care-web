import './bootstrap';
import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';

window.Alpine = Alpine;
Alpine.start();

const canvas = document.querySelector('#climate-chart');

if (canvas instanceof HTMLCanvasElement) {
    const data = JSON.parse(canvas.dataset.chart ?? '{}');
    const colors = getComputedStyle(document.documentElement);
    const green = colors.getPropertyValue('--color-berry-green').trim();
    const amber = '#f59e0b';
    const crimson = colors.getPropertyValue('--color-berry-crimson').trim();
    const grid = colors.getPropertyValue('--color-berry-border').trim();
    const muted = colors.getPropertyValue('--color-berry-slate-400').trim();

    new Chart(canvas, {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [
                {
                    label: 'Kelembapan (%)',
                    data: data.humidity,
                    yAxisID: 'humidity',
                    borderColor: green,
                    backgroundColor: 'rgb(21 128 61 / 7%)',
                    fill: true,
                    tension: 0.38,
                    pointRadius: 0,
                    pointHoverRadius: 4,
                    borderWidth: 2,
                },
                {
                    label: 'Suhu (°C)',
                    data: data.temperature,
                    yAxisID: 'temperature',
                    borderColor: amber,
                    backgroundColor: 'transparent',
                    tension: 0.38,
                    pointRadius: 0,
                    pointHoverRadius: 4,
                    borderWidth: 2,
                },
                {
                    label: 'Ambang RH',
                    data: Array(data.labels.length).fill(data.threshold),
                    yAxisID: 'humidity',
                    borderColor: crimson,
                    borderDash: [5, 5],
                    pointRadius: 0,
                    borderWidth: 1,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { intersect: false, mode: 'index' },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#0f172a',
                    padding: 10,
                    titleFont: { family: 'Inter', size: 11 },
                    bodyFont: { family: 'Inter', size: 10 },
                },
            },
            scales: {
                x: {
                    grid: { display: false },
                    border: { display: false },
                    ticks: { color: muted, font: { family: 'Inter', size: 9 }, maxRotation: 0 },
                },
                humidity: {
                    type: 'linear',
                    position: 'left',
                    min: 20,
                    max: 100,
                    grid: { color: grid },
                    border: { display: false },
                    ticks: { color: muted, stepSize: 20, font: { family: 'Inter', size: 9 } },
                },
                temperature: {
                    type: 'linear',
                    position: 'right',
                    min: 15,
                    max: 35,
                    grid: { display: false },
                    border: { display: false },
                    ticks: { display: false },
                },
            },
        },
    });
}
