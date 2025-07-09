import { AlertTriangle, Package, DollarSign, Clock } from 'lucide-react'
import type { ReportStats } from '../types'
import { formatCurrency } from '../lib/utils'

interface ReportStatsProps {
  stats: ReportStats
}

export function ReportStats({ stats }: ReportStatsProps) {
  const statCards = [
    {
      title: 'Total Items',
      value: stats.total.toString(),
      icon: Package,
      color: 'text-primary-600',
      bgColor: 'bg-primary-50'
    },
    {
      title: 'Damaged Items',
      value: stats.damaged.toString(),
      icon: AlertTriangle,
      color: 'text-error-600',
      bgColor: 'bg-error-50'
    },
    {
      title: 'Lost Items',
      value: stats.lost.toString(),
      icon: Package,
      color: 'text-error-600',
      bgColor: 'bg-error-50'
    },
    {
      title: 'Overdue Items',
      value: stats.overdue.toString(),
      icon: Clock,
      color: 'text-warning-600',
      bgColor: 'bg-warning-50'
    },
    {
      title: 'Total Value',
      value: formatCurrency(stats.totalValue),
      icon: DollarSign,
      color: 'text-success-600',
      bgColor: 'bg-success-50'
    }
  ]

  return (
    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
      {statCards.map((stat) => {
        const Icon = stat.icon
        return (
          <div key={stat.title} className="card p-6">
            <div className="flex items-center">
              <div className={`${stat.bgColor} p-3 rounded-lg`}>
                <Icon className={`h-6 w-6 ${stat.color}`} />
              </div>
              <div className="ml-4">
                <p className="text-sm font-medium text-gray-600">{stat.title}</p>
                <p className="text-2xl font-bold text-gray-900">{stat.value}</p>
              </div>
            </div>
          </div>
        )
      })}
    </div>
  )
}