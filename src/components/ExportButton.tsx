import { Download } from 'lucide-react'
import type { ItemWithCheckout } from '../types'
import { formatCurrency, formatDate, isOverdue } from '../lib/utils'

interface ExportButtonProps {
  items: ItemWithCheckout[]
  filter: string
}

export function ExportButton({ items, filter }: ExportButtonProps) {
  const exportToCSV = () => {
    const headers = [
      'Name',
      'Category',
      'Serial Number',
      'Status',
      'Condition',
      'Current Value',
      'Location',
      'Checked Out To',
      'Due Date',
      'Days Overdue',
      'Notes'
    ]

    const csvData = items.map(item => {
      const daysOverdue = item.checkout && isOverdue(item.checkout.due_date)
        ? Math.ceil((Date.now() - new Date(item.checkout.due_date).getTime()) / (1000 * 60 * 60 * 24))
        : 0

      return [
        item.name,
        item.category,
        item.serial_number || '',
        item.status,
        item.condition,
        item.current_value || item.purchase_price || 0,
        item.location || '',
        item.checkout?.user_name || '',
        item.checkout ? formatDate(item.checkout.due_date) : '',
        daysOverdue > 0 ? daysOverdue : '',
        item.notes || ''
      ]
    })

    const csvContent = [headers, ...csvData]
      .map(row => row.map(cell => `"${cell}"`).join(','))
      .join('\n')

    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' })
    const link = document.createElement('a')
    const url = URL.createObjectURL(blob)
    
    link.setAttribute('href', url)
    link.setAttribute('download', `item-report-${filter}-${new Date().toISOString().split('T')[0]}.csv`)
    link.style.visibility = 'hidden'
    
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
  }

  return (
    <button
      onClick={exportToCSV}
      className="btn-secondary"
      disabled={items.length === 0}
    >
      <Download className="h-4 w-4 mr-2" />
      Export CSV
    </button>
  )
}