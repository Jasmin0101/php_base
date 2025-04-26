let chartInstance = null;

function renderChart(ctx, labels, data, labelText) {
  if (chartInstance) {
    chartInstance.destroy();
  }

  chartInstance = new Chart(ctx, {
    type: "line",
    data: {
      labels: labels,
      datasets: [
        {
          label: labelText,
          data: data,
          borderColor: "blue",
          borderWidth: 2,
          backgroundColor: "rgba(0, 0, 255, 0.1)",
          tension: 0.3,
          fill: true,
        },
      ],
    },
    options: {
      responsive: true,
      scales: {
        x: {
          ticks: { autoSkip: true, maxTicksLimit: 10 },
        },
        y: {
          beginAtZero: false,
        },
      },
    },
  });
}

function setTimeframe(range) {
  const now = new Date();
  const filteredLabels = [];
  const filteredData = [];

  for (let i = 0; i < originalLabels.length; i++) {
    const date = new Date(originalLabels[i]);
    const diff = (now - date) / (1000 * 60 * 60 * 24); // разница в днях

    if (
      (range === "day" && diff <= 1) ||
      (range === "week" && diff <= 7) ||
      (range === "month" && diff <= 30) ||
      range === "all"
    ) {
      filteredLabels.push(originalLabels[i]);
      filteredData.push(originalData[i]);
    }
  }

  renderChart(
    document.getElementById("myChart"),
    filteredLabels,
    filteredData,
    "Stocks price change"
  );
}
