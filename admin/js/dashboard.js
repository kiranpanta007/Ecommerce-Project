document.addEventListener("DOMContentLoaded", () => {
  const totalRevenueEl = document.getElementById("total-revenue");
  const totalOrdersEl = document.getElementById("total-orders");
  const newCustomersEl = document.getElementById("new-customers");
  const inventoryAlertEl = document.getElementById("inventory-alert");
  const recentOrdersListEl = document.getElementById("recent-orders-list");

  const salesCtx = document.getElementById("salesChart").getContext("2d");
  const orderStatusCtx = document.getElementById("orderStatusChart").getContext("2d");
  const revenueSourceCtx = document.getElementById("revenueSourceChart").getContext("2d");

  let salesChart, orderStatusChart, revenueSourceChart;

  function randomColor(alpha=1) {
    return `rgba(${Math.floor(Math.random()*255)},${Math.floor(Math.random()*255)},${Math.floor(Math.random()*255)},${alpha})`;
  }

  async function fetchDashboardData() {
    try {
      const res = await fetch("dashboard_data.php");
      if (!res.ok) throw new Error("Network error");
      const data = await res.json();

      totalRevenueEl.textContent = `$${Number(data.total_revenue).toLocaleString()}`;
      totalOrdersEl.textContent = data.total_orders;
      newCustomersEl.textContent = data.new_customers;
      inventoryAlertEl.textContent = data.inventory_alert;

      // Prepare last 7 days
      const last7days = [];
      for(let i=6; i>=0; i--) {
        const d = new Date();
        d.setDate(d.getDate() - i);
        last7days.push(d.toISOString().split('T')[0]);
      }

      const ordersByDay = last7days.map(date => {
        const item = data.sales_analytics.find(e => e.date === date);
        return item ? Number(item.orders_count) : 0;
      });
      const revenueByDay = last7days.map(date => {
        const item = data.sales_analytics.find(e => e.date === date);
        return item ? Number(item.revenue) : 0;
      });

      if (!salesChart) {
        salesChart = new Chart(salesCtx, {
          type: "bar",
          data: {
            labels: last7days,
            datasets: [
              {
                label: "Orders",
                data: ordersByDay,
                backgroundColor: "rgba(54, 162, 235, 0.7)",
                yAxisID: 'y',
              },
              {
                label: "Revenue ($)",
                data: revenueByDay,
                backgroundColor: "rgba(255, 206, 86, 0.7)",
                yAxisID: 'y1',
              },
            ],
          },
          options: {
            responsive: true,
            interaction: { mode: 'index', intersect: false },
            stacked: false,
            scales: {
              y: { type: 'linear', display: true, position: 'left', beginAtZero: true, title: {display:true, text:'Orders'} },
              y1: { type: 'linear', display: true, position: 'right', beginAtZero: true, grid: { drawOnChartArea: false }, title: {display:true, text:'Revenue ($)'} }
            }
          }
        });
      } else {
        salesChart.data.labels = last7days;
        salesChart.data.datasets[0].data = ordersByDay;
        salesChart.data.datasets[1].data = revenueByDay;
        salesChart.update();
      }

      // Order Status chart
      const statusLabels = Object.keys(data.order_status);
      const statusCounts = Object.values(data.order_status);
      const statusColors = statusLabels.map(() => randomColor(0.7));
      if (!orderStatusChart) {
        orderStatusChart = new Chart(orderStatusCtx, {
          type: "doughnut",
          data: { labels: statusLabels, datasets: [{ data: statusCounts, backgroundColor: statusColors }] },
          options: { responsive: true, plugins: { legend: { position: "bottom" } } }
        });
      } else {
        orderStatusChart.data.labels = statusLabels;
        orderStatusChart.data.datasets[0].data = statusCounts;
        orderStatusChart.update();
      }

      // Revenue source chart
      const paymentLabels = data.revenue_source.map(s => s.payment_method);
      const paymentRevenues = data.revenue_source.map(s => s.revenue);
      const paymentColors = paymentLabels.map(() => randomColor(0.7));
      if (!revenueSourceChart) {
        revenueSourceChart = new Chart(revenueSourceCtx, {
          type: "pie",
          data: { labels: paymentLabels, datasets: [{ data: paymentRevenues, backgroundColor: paymentColors }] },
          options: { responsive: true, plugins: { legend: { position: "bottom" } } }
        });
      } else {
        revenueSourceChart.data.labels = paymentLabels;
        revenueSourceChart.data.datasets[0].data = paymentRevenues;
        revenueSourceChart.update();
      }

      // Recent orders placeholder (your API doesn’t provide them, so we show last 5 days' summary)
      let recentHTML = "<table><thead><tr><th>Date</th><th>Orders</th><th>Revenue ($)</th></tr></thead><tbody>";
      for (let i = last7days.length - 1; i >= last7days.length - 5; i--) {
        if (i < 0) continue;
        recentHTML += `<tr><td>${last7days[i]}</td><td>${ordersByDay[i]}</td><td>${revenueByDay[i].toFixed(2)}</td></tr>`;
      }
      recentHTML += "</tbody></table>";
      recentOrdersListEl.innerHTML = recentHTML;

    } catch (e) {
      console.error("Error loading dashboard data:", e);
      totalRevenueEl.textContent = "Error";
      totalOrdersEl.textContent = "Error";
      newCustomersEl.textContent = "Error";
      inventoryAlertEl.textContent = "Error";
      recentOrdersListEl.textContent = "Failed to load recent orders.";
    }
  }

  fetchDashboardData();
  setInterval(fetchDashboardData, 15000);
});
