import { AlertTriangle, Clock, Package, User } from 'lucide-react'
import type { ItemWithCheckout } from '../types'
import { formatCurrency, formatDate, isOverdue } from '../lib/utils'

interface ItemsTableProps {
  items: ItemWithCheckout[]
  loading: boolean
}

export function ItemsTable({ items, loading }: ItemsTableProps) {
  const getStatusBadge = (item: ItemWithCheckout) => {
    if (item.status === 'damaged' || item.condition === 'damaged') {
      return <span className="badge-error">Damaged</span>
    }
    if (item.status === 'lost') {
      return <span className="badge-error">Lost</span>
    }
    if (item.checkout && item.checkout.status === 'active' && isOverdue(item.checkout.due_date)) {
      return <span className="badge-warning">Overdue</span>
    }
    if (item.status === 'checked_out') {
      return <span className="badge-gray">Checked Out</span>
    }
    return <span className="badge-success">Available</span>
  }

  const getStatusIcon = (item: ItemWithCheckout) => {
    if (item.status === 'damaged' || item.condition === 'damaged') {
      return <AlertTriangle className="h-4 w-4 text-error-500" />
    }
    if (item.status === 'lost') {
      return <Package className="h-4 w-4 text-error-500" />
    }
    if (item.checkout && item.checkout.status === 'active' && isOverdue(item.checkout.due_date)) {
      return <Clock className="h-4 w-4 text-warning-500" />
    }
    return null
  }

  if (loading) {
    return (
      <div className="card p-8">
        <div className="flex items-center justify-center">
          <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
          <span className="ml-3 text-gray-600">Loading items...</span>
        </div>
      </div>
    )
  }

  if (items.length === 0) {
    return (
      <div className="card p-8 text-center">
        <Package className="h-12 w-12 text-gray-400 mx-auto mb-4" />
        <h3 className="text-lg font-medium text-gray-900 mb-2">No items found</h3>
        <p className="text-gray-600">No items match the current filter criteria.</p>
      </div>
    )
  }

  return (
    <div className="card overflow-hidden">
      <div className="overflow-x-auto">
        <table className="min-w-full divide-y divide-gray-200">
          <thead className="bg-gray-50">
            <tr>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Item
              </th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Category
              </th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Status
              </th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Value
              </th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Location
              </th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Checkout Info
              </th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Notes
              </th>
            </tr>
          </thead>
          <tbody className="bg-white divide-y divide-gray-200">
            {items.map((item) => (
              <tr key={item.id} className="hover:bg-gray-50">
                <td className="px-6 py-4 whitespace-nowrap">
                  <div className="flex items-center">
                    {getStatusIcon(item)}
                    <div className="ml-3">
                      <div className="text-sm font-medium text-gray-900">
                        {item.name}
                      </div>
                      {item.serial_number && (
                        <div className="text-sm text-gray-500">
                          S/N: {item.serial_number}
                        </div>
                      )}
                    </div>
                  </div>
                </td>
                <td className="px-6 py-4 whitespace-nowrap">
                  <span className="badge-gray">{item.category}</span>
                </td>
                <td className="px-6 py-4 whitespace-nowrap">
                  {getStatusBadge(item)}
                </td>
                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {formatCurrency(item.current_value || item.purchase_price)}
                </td>
                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {item.location || 'N/A'}
                </td>
                <td className="px-6 py-4 whitespace-nowrap">
                  {item.checkout ? (
                    <div className="text-sm">
                      <div className="flex items-center text-gray-900">
                        <User className="h-4 w-4 mr-1" />
                        {item.checkout.user_name}
                      </div>
                      <div className="text-gray-500">
                        Due: {formatDate(item.checkout.due_date)}
                      </div>
                      {isOverdue(item.checkout.due_date) && (
                        <div className="text-error-600 font-medium">
                          {Math.ceil((Date.now() - new Date(item.checkout.due_date).getTime()) / (1000 * 60 * 60 * 24))} days overdue
                        </div>
                      )}
                    </div>
                  ) : (
                    <span className="text-gray-500">Not checked out</span>
                  )}
                </td>
                <td className="px-6 py-4">
                  <div className="text-sm text-gray-900 max-w-xs truncate">
                    {item.notes || 'No notes'}
                  </div>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  )
}