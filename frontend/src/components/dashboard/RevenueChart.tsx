import React from 'react';

const RevenueChart: React.FC = () => {
  // Mock data for the chart
  const chartData = [
    { month: 'Jan', revenue: 45000 },
    { month: 'Feb', revenue: 52000 },
    { month: 'Mar', revenue: 48000 },
    { month: 'Apr', revenue: 61000 },
    { month: 'May', revenue: 55000 },
    { month: 'Jun', revenue: 67000 },
  ];

  const maxRevenue = Math.max(...chartData.map(d => d.revenue));

  return (
    <div className="bg-gray-900/40 backdrop-blur-xl border border-gray-700/50 rounded-2xl p-6">
      <div className="flex items-center justify-between mb-6">
        <h3 className="text-xl font-semibold text-white">Revenue Overview</h3>
        <div className="flex items-center space-x-2">
          <div className="w-3 h-3 bg-gradient-to-r from-blue-500 to-cyan-500 rounded-full"></div>
          <span className="text-gray-400 text-sm">Monthly Revenue</span>
        </div>
      </div>

      {/* Simple Bar Chart */}
      <div className="space-y-4">
        {chartData.map((data, index) => (
          <div key={index} className="flex items-center space-x-4">
            <div className="w-8 text-gray-400 text-sm">{data.month}</div>
            <div className="flex-1 bg-gray-800/50 rounded-full h-8 relative overflow-hidden">
              <div
                className="h-full bg-gradient-to-r from-blue-500 to-cyan-500 rounded-full transition-all duration-1000 ease-out flex items-center justify-end pr-3"
                style={{ width: `${(data.revenue / maxRevenue) * 100}%` }}
              >
                <span className="text-white text-xs font-medium">
                  ${(data.revenue / 1000).toFixed(0)}k
                </span>
              </div>
            </div>
          </div>
        ))}
      </div>

      {/* Summary */}
      <div className="mt-6 pt-6 border-t border-gray-700/50">
        <div className="flex items-center justify-between">
          <div>
            <p className="text-gray-400 text-sm">Total Revenue (6 months)</p>
            <p className="text-white text-2xl font-bold">
              ${chartData.reduce((sum, d) => sum + d.revenue, 0).toLocaleString()}
            </p>
          </div>
          <div className="text-right">
            <p className="text-gray-400 text-sm">Average Monthly</p>
            <p className="text-white text-xl font-semibold">
              ${Math.round(chartData.reduce((sum, d) => sum + d.revenue, 0) / chartData.length).toLocaleString()}
            </p>
          </div>
        </div>
      </div>
    </div>
  );
};

export default RevenueChart;