import { Filter } from 'lucide-react'
import type { ReportFilter } from '../types'

interface ReportFiltersProps {
  currentFilter: ReportFilter
  onFilterChange: (filter: ReportFilter) => void
  stats: {
    total: number
    damaged: number
    lost: number
    overdue: number
  }
}

export function ReportFilters({ currentFilter, onFilterChange, stats }: ReportFiltersProps) {
  const filters = [
    { key: 'all' as ReportFilter, label: 'All Items', count: stats.total },
    { key: 'damaged' as ReportFilter, label: 'Damaged', count: stats.damaged },
    { key: 'lost' as ReportFilter, label: 'Lost', count: stats.lost },
    { key: 'overdue' as ReportFilter, label: 'Overdue', count: stats.overdue }
  ]

  return (
    <div className="card p-6 mb-6">
      <div className="flex items-center gap-4 mb-4">
        <Filter className="h-5 w-5 text-gray-600" />
        <h3 className="text-lg font-semibold text-gray-900">Filter Items</h3>
      </div>
      
      <div className="flex flex-wrap gap-2">
        {filters.map((filter) => (
          <button
            key={filter.key}
            onClick={() => onFilterChange(filter.key)}
            className={`inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-colors ${
              currentFilter === filter.key
                ? 'bg-primary-600 text-white'
                : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
            }`}
          >
            {filter.label}
            <span className={`px-2 py-0.5 rounded-full text-xs ${
              currentFilter === filter.key
                ? 'bg-primary-500 text-white'
                : 'bg-gray-200 text-gray-600'
            }`}>
              {filter.count}
            </span>
          </button>
        ))}
      </div>
    </div>
  )
}