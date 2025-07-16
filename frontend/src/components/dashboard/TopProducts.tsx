import React from 'react';
import { TrendingUp } from 'lucide-react';

interface Product {
  id: string;
  name: string;
  sales: number;
  revenue: number;
  change: number;
}

const TopProducts: React.FC = () => {
  const products: Product[] = [
    { id: '1', name: 'Wireless Headphones', sales: 234, revenue: 23400, change: 12.5 },
    { id: '2', name: 'Smart Watch', sales: 189, revenue: 37800, change: 8.3 },
    { id: '3', name: 'Laptop Stand', sales: 156, revenue: 7800, change: -2.1 },
    { id: '4', name: 'USB-C Cable', sales: 298, revenue: 5960, change: 15.7 },
    { id: '5', name: 'Phone Case', sales: 167, revenue: 3340, change: 5.2 },
  ];

  return (
    <div className="bg-gray-900/40 backdrop-blur-xl border border-gray-700/50 rounded-2xl p-6">
      <div className="flex items-center justify-between mb-6">
        <h3 className="text-xl font-semibold text-white">Top Products</h3>
        <TrendingUp className="h-5 w-5 text-green-400" />
      </div>

      <div className="space-y-4">
        {products.map((product, index) => (
          <div key={product.id} className="flex items-center justify-between">
            <div className="flex items-center space-x-3">
              <div className="w-8 h-8 bg-gradient-to-br from-blue-500/20 to-cyan-500/20 border border-blue-500/30 rounded-lg flex items-center justify-center">
                <span className="text-blue-400 text-sm font-bold">{index + 1}</span>
              </div>
              <div>
                <p className="text-white text-sm font-medium">{product.name}</p>
                <p className="text-gray-400 text-xs">{product.sales} sold</p>
              </div>
            </div>

            <div className="text-right">
              <p className="text-white text-sm font-semibold">${product.revenue.toLocaleString()}</p>
              <div className={`flex items-center space-x-1 text-xs ${
                product.change >= 0 ? 'text-green-400' : 'text-red-400'
              }`}>
                <TrendingUp className={`h-3 w-3 ${product.change < 0 ? 'rotate-180' : ''}`} />
                <span>{Math.abs(product.change)}%</span>
              </div>
            </div>
          </div>
        ))}
      </div>

      <div className="mt-6 pt-4 border-t border-gray-700/50">
        <button className="w-full text-blue-400 hover:text-blue-300 text-sm font-medium transition-colors">
          View All Products
        </button>
      </div>
    </div>
  );
};

export default TopProducts;