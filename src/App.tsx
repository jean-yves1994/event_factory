import { useState } from 'react'
import { FileText, RefreshCw } from 'lucide-react'
import { useItems } from './hooks/useItems'
import { ReportStats } from './components/ReportStats'
import { ReportFilters } from './components/ReportFilters'
import { ItemsTable } from './components/ItemsTable'
import { ExportButton } from './components/ExportButton'
import type { ReportFilter } from './types'

function App() {
  const { items, loading, error, refetch, getFilteredItems, getReportStats } = useItems()
  const [currentFilter, setCurrentFilter] = useState<ReportFilter>('all')

  const stats = getReportStats()
  const filteredItems = getFilteredItems(currentFilter)

  const handleRefresh = () => {
    refetch()
  }

  if (error) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <div className="card p-8 max-w-md w-full text-center">
          <div className="text-error-500 mb-4">
            <FileText className="h-12 w-12 mx-auto" />
          </div>
          <h2 className="text-xl font-semibold text-gray-900 mb-2">Connection Error</h2>
          <p className="text-gray-600 mb-4">{error}</p>
          <p className="text-sm text-gray-500 mb-6">
            Please click the "Connect to Supabase" button in the top right to set up your database connection.
          </p>
          <button onClick={handleRefresh} className="btn-primary">
            <RefreshCw className="h-4 w-4 mr-2" />
            Retry Connection
          </button>
        </div>
      </div>
    )
  }

  return (
    <div className="min-h-screen bg-gray-50">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {/* Header */}
        <div className="mb-8">
          <div className="flex items-center justify-between">
            <div>
              <h1 className="text-3xl font-bold text-gray-900 flex items-center gap-3">
                <FileText className="h-8 w-8 text-primary-600" />
                Item Report Dashboard
              </h1>
              <p className="text-gray-600 mt-2">
                Track damaged, lost, and overdue items in your inventory
              </p>
            </div>
            <div className="flex items-center gap-3">
              <ExportButton items={filteredItems} filter={currentFilter} />
              <button
                onClick={handleRefresh}
                disabled={loading}
                className="btn-secondary"
              >
                <RefreshCw className={`h-4 w-4 mr-2 ${loading ? 'animate-spin' : ''}`} />
                Refresh
              </button>
            </div>
          </div>
        </div>

        {/* Stats Overview */}
        <ReportStats stats={stats} />

        {/* Filters */}
        <ReportFilters
          currentFilter={currentFilter}
          onFilterChange={setCurrentFilter}
          stats={stats}
        />

        {/* Items Table */}
        <div className="mb-6">
          <div className="flex items-center justify-between mb-4">
            <h2 className="text-xl font-semibold text-gray-900">
              {currentFilter === 'all' ? 'All Items' : 
               currentFilter === 'damaged' ? 'Damaged Items' :
               currentFilter === 'lost' ? 'Lost Items' : 'Overdue Items'}
            </h2>
            <span className="text-sm text-gray-600">
              Showing {filteredItems.length} of {items.length} items
            </span>
          </div>
          <ItemsTable items={filteredItems} loading={loading} />
        </div>
      </div>
    </div>
  )
}

export default App