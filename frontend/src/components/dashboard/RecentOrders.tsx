import React from 'react';
import { Clock, CheckCircle, XCircle, AlertCircle } from 'lucide-react';

interface Order {
  id: string;
  customer: string;
  amount: number;
  status: 'pending' | 'processing' | 'completed' | 'cancelled';
  time: string;
}

const RecentOrders: React.FC = () => {
  const orders: Order[] = [
    { id: '#1234', customer: 'John Smith', amount: 299.99, status: 'completed', time: '2 min ago' },
    { id: '#1235', customer: 'Sarah Johnson', amount: 149.50, status: 'processing', time: '5 min ago' },
    { id: '#1236', customer: 'Mike Wilson', amount: 89.99, status: 'pending', time: '8 min ago' },
    { id: '#1237', customer: 'Emily Davis', amount: 199.99, status: 'completed', time: '12 min ago' },
    { id: '#1238', customer: 'David Brown', amount: 79.99, status: 'cancelled', time: '15 min ago' },
  ];

  const getStatusIcon = (status: Order['status']) => {
    switch (status) {
      case 'completed':
        return <CheckCircle className="h-4 w-4 text-green-400" />;
      case 'processing':
        return <AlertCircle className="h-4 w-4 text-yellow-400" />;
      case 'pending':
        return <Clock className="h-4 w-4 text-blue-400" />;
      case 'cancelled':
        return <XCircle className="h-4 w-4 text-red-400" />;
    }
  };

  const getStatusColor = (status: Order['status']) => {
    switch (status) {
      case 'completed':
        return 'text-green-400 bg-green-500/10';
      case 'processing':
        return 'text-yellow-400 bg-yellow-500/10';
      case 'pending':
        return 'text-blue-400 bg-blue-500/10';
      case 'cancelled':
        return 'text-red-400 bg-red-500/10';
    }
  };

  return (
    <div className="bg-gray-900/40 backdrop-blur-xl border border-gray-700/50 rounded-2xl p-6">
      <div className="flex items-center justify-between mb-6">
        <h3 className="text-xl font-semibold text-white">Recent Orders</h3>
        <button className="text-blue-400 hover:text-blue-300 text-sm font-medium transition-colors">
          View All
        </button>
      </div>

      <div className="space-y-4">
        {orders.map((order) => (
          <div
            key={order.id}
            className="flex items-center justify-between p-4 bg-gray-800/30 rounded-xl hover:bg-gray-800/50 transition-colors"
          >
            <div className="flex items-center space-x-4">
              <div className="flex items-center space-x-2">
                {getStatusIcon(order.status)}
                <span className="text-white font-medium">{order.id}</span>
              </div>
              <div>
                <p className="text-white text-sm">{order.customer}</p>
                <p className="text-gray-400 text-xs">{order.time}</p>
              </div>
            </div>

            <div className="flex items-center space-x-4">
              <span className="text-white font-semibold">${order.amount}</span>
              <span className={`px-2 py-1 rounded-full text-xs font-medium capitalize ${getStatusColor(order.status)}`}>
                {order.status}
              </span>
            </div>
          </div>
        ))}
      </div>

      <div className="mt-6 pt-4 border-t border-gray-700/50">
        <div className="flex items-center justify-between text-sm">
          <span className="text-gray-400">Total Orders Today</span>
          <span className="text-white font-semibold">47 orders</span>
        </div>
      </div>
    </div>
  );
};

export default RecentOrders;