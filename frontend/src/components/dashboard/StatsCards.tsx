import React from 'react';
import { TrendingUp, TrendingDown, DollarSign, ShoppingCart, Users, Package } from 'lucide-react';

interface StatCard {
  title: string;
  value: string;
  change: number;
  changeType: 'increase' | 'decrease';
  icon: React.ReactNode;
  color: string;
}

const StatsCards: React.FC = () => {
  const stats: StatCard[] = [
    {
      title: 'Total Revenue',
      value: '$124,563',
      change: 12.5,
      changeType: 'increase',
      icon: <DollarSign className="h-6 w-6" />,
      color: 'from-green-500/20 to-emerald-500/20 border-green-500/30'
    },
    {
      title: 'New Orders',
      value: '1,429',
      change: 8.2,
      changeType: 'increase',
      icon: <ShoppingCart className="h-6 w-6" />,
      color: 'from-blue-500/20 to-cyan-500/20 border-blue-500/30'
    },
    {
      title: 'Total Customers',
      value: '9,847',
      change: 3.1,
      changeType: 'decrease',
      icon: <Users className="h-6 w-6" />,
      color: 'from-purple-500/20 to-pink-500/20 border-purple-500/30'
    },
    {
      title: 'Products Sold',
      value: '2,847',
      change: 15.3,
      changeType: 'increase',
      icon: <Package className="h-6 w-6" />,
      color: 'from-orange-500/20 to-red-500/20 border-orange-500/30'
    }
  ];

  return (
    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      {stats.map((stat, index) => (
        <div
          key={index}
          className={`bg-gradient-to-br ${stat.color} backdrop-blur-xl border rounded-2xl p-6 hover:scale-105 transition-all duration-300`}
        >
          <div className="flex items-center justify-between mb-4">
            <div className="text-gray-300">
              {stat.icon}
            </div>
            <div className={`flex items-center space-x-1 text-sm ${
              stat.changeType === 'increase' ? 'text-green-400' : 'text-red-400'
            }`}>
              {stat.changeType === 'increase' ? (
                <TrendingUp className="h-4 w-4" />
              ) : (
                <TrendingDown className="h-4 w-4" />
              )}
              <span>{stat.change}%</span>
            </div>
          </div>
          
          <div>
            <h3 className="text-2xl font-bold text-white mb-1">{stat.value}</h3>
            <p className="text-gray-400 text-sm">{stat.title}</p>
          </div>
        </div>
      ))}
    </div>
  );
};

export default StatsCards;