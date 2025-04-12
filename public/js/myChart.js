function renderChart(ctx, x, y, label) {
  new Chart(ctx, {
    type: "line",
    data: {
      labels: x,
      datasets: [
        {
          label: label,
          data: y,
          borderWidth: 1,
        },
      ],
    },
  });
}
