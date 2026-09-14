const CHART_COLORS = {
    parameter: '#2a78d6',
    livestock: '#eb6834',
    gridline: '#e1e0d9',
    axisText: '#898781',
    ink: '#0b0b0b',
};

const CHART_FONT = { family: 'monospace' };

const tankSelect = document.getElementById('graph_tank_id');
const parameterSelect = document.getElementById('graph_parameter');
const parameterCanvas = document.getElementById('parameter-chart');
const parameterEmpty = document.getElementById('parameter-chart-empty');
const livestockCanvas = document.getElementById('livestock-chart');
const livestockEmpty = document.getElementById('livestock-chart-empty');

let parameterChart = null;
let livestockChart = null;
let currentWaterTests = [];

function formatLabel(dateString) {
    return new Date(dateString.replace(' ', 'T')).toLocaleString(undefined, {
        year: 'numeric', month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit',
    });
}

function baseScaleOptions() {
    return {
        grid: { color: CHART_COLORS.gridline },
        ticks: { color: CHART_COLORS.axisText, font: CHART_FONT },
    };
}

function renderParameterChart(parameterName) {
    const readings = currentWaterTests
        .filter((reading) => reading.parameter_name === parameterName)
        .slice()
        .sort((a, b) => new Date(a.tested_at) - new Date(b.tested_at));

    if (parameterChart) {
        parameterChart.destroy();
        parameterChart = null;
    }

    if (readings.length === 0) {
        parameterCanvas.hidden = true;
        parameterEmpty.hidden = false;
        return;
    }

    parameterCanvas.hidden = false;
    parameterEmpty.hidden = true;

    parameterChart = new Chart(parameterCanvas, {
        type: 'line',
        data: {
            labels: readings.map((reading) => formatLabel(reading.tested_at)),
            datasets: [{
                label: parameterName,
                data: readings.map((reading) => Number(reading.value)),
                borderColor: CHART_COLORS.parameter,
                backgroundColor: CHART_COLORS.parameter,
                borderWidth: 2,
                pointRadius: 3,
                pointHoverRadius: 6,
                tension: 0,
            }],
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                title: {
                    display: true,
                    text: `${parameterName}${readings[0].unit ? ' (' + readings[0].unit + ')' : ''} over time`,
                    color: CHART_COLORS.ink,
                    font: CHART_FONT,
                },
            },
            scales: {
                x: baseScaleOptions(),
                y: baseScaleOptions(),
            },
        },
    });
}

function populateParameterSelect() {
    const parameters = [...new Set(currentWaterTests.map((reading) => reading.parameter_name))];

    if (parameters.length === 0) {
        parameterSelect.innerHTML = '<option value="">No data yet</option>';
        renderParameterChart(null);
        return;
    }

    parameterSelect.innerHTML = '';
    for (const parameter of parameters) {
        const option = document.createElement('option');
        option.value = parameter;
        option.textContent = parameter;
        parameterSelect.appendChild(option);
    }

    renderParameterChart(parameterSelect.value);
}

function renderLivestockChart(organisms) {
    const sorted = organisms.slice().sort((a, b) => new Date(a.date_added) - new Date(b.date_added));

    if (livestockChart) {
        livestockChart.destroy();
        livestockChart = null;
    }

    if (sorted.length === 0) {
        livestockCanvas.hidden = true;
        livestockEmpty.hidden = false;
        return;
    }

    livestockCanvas.hidden = false;
    livestockEmpty.hidden = true;

    let runningCount = 0;
    const labels = [];
    const counts = [];
    for (const organism of sorted) {
        runningCount += 1;
        labels.push(organism.date_added);
        counts.push(runningCount);
    }

    livestockChart = new Chart(livestockCanvas, {
        type: 'line',
        data: {
            labels,
            datasets: [{
                label: 'Organism Count',
                data: counts,
                borderColor: CHART_COLORS.livestock,
                backgroundColor: CHART_COLORS.livestock,
                borderWidth: 2,
                pointRadius: 3,
                pointHoverRadius: 6,
                tension: 0,
                stepped: true,
            }],
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                title: { display: true, text: 'Livestock count over time', color: CHART_COLORS.ink, font: CHART_FONT },
            },
            scales: {
                x: baseScaleOptions(),
                y: { ...baseScaleOptions(), beginAtZero: true, ticks: { ...baseScaleOptions().ticks, stepSize: 1 } },
            },
        },
    });
}

async function loadTankData(tankId) {
    const [waterTests, organisms] = await Promise.all([
        fetch(`api/water_tests.php?tank_id=${tankId}`).then((r) => r.json()),
        fetch(`api/organisms.php?tank_id=${tankId}`).then((r) => r.json()),
    ]);

    currentWaterTests = waterTests;
    populateParameterSelect();
    renderLivestockChart(organisms);
}

async function loadTanks() {
    const response = await fetch('api/tanks.php');
    const tanks = await response.json();

    tankSelect.innerHTML = '';
    for (const tank of tanks) {
        const option = document.createElement('option');
        option.value = tank.id;
        option.textContent = tank.custom_name;
        tankSelect.appendChild(option);
    }

    if (tanks.length > 0) {
        await loadTankData(tanks[0].id);
    }
}

tankSelect.addEventListener('change', () => {
    if (tankSelect.value) {
        loadTankData(tankSelect.value);
    }
});

parameterSelect.addEventListener('change', () => {
    renderParameterChart(parameterSelect.value);
});

loadTanks();
