import { useState, useEffect } from 'react'
import { supabase } from '../lib/supabase'
import type { Item, Checkout, ItemWithCheckout, ReportFilter } from '../types'
import { isOverdue } from '../lib/utils'

export function useItems() {
  const [items, setItems] = useState<ItemWithCheckout[]>([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)

  const fetchItems = async () => {
    try {
      setLoading(true)
      setError(null)

      // Fetch items
      const { data: itemsData, error: itemsError } = await supabase
        .from('items')
        .select('*')
        .order('created_at', { ascending: false })

      if (itemsError) throw itemsError

      // Fetch active checkouts
      const { data: checkoutsData, error: checkoutsError } = await supabase
        .from('checkouts')
        .select('*')
        .eq('status', 'active')

      if (checkoutsError) throw checkoutsError

      // Combine items with their checkout information
      const itemsWithCheckouts: ItemWithCheckout[] = (itemsData || []).map(item => {
        const checkout = (checkoutsData || []).find(c => c.item_id === item.id)
        return {
          ...item,
          checkout
        }
      })

      setItems(itemsWithCheckouts)
    } catch (err) {
      setError(err instanceof Error ? err.message : 'An error occurred')
    } finally {
      setLoading(false)
    }
  }

  useEffect(() => {
    fetchItems()
  }, [])

  const getFilteredItems = (filter: ReportFilter): ItemWithCheckout[] => {
    switch (filter) {
      case 'damaged':
        return items.filter(item => item.status === 'damaged' || item.condition === 'damaged')
      case 'lost':
        return items.filter(item => item.status === 'lost')
      case 'overdue':
        return items.filter(item => 
          item.checkout && 
          item.checkout.status === 'active' && 
          isOverdue(item.checkout.due_date)
        )
      default:
        return items
    }
  }

  const getReportStats = () => {
    const damaged = items.filter(item => item.status === 'damaged' || item.condition === 'damaged').length
    const lost = items.filter(item => item.status === 'lost').length
    const overdue = items.filter(item => 
      item.checkout && 
      item.checkout.status === 'active' && 
      isOverdue(item.checkout.due_date)
    ).length
    
    const totalValue = items.reduce((sum, item) => {
      return sum + (item.current_value || item.purchase_price || 0)
    }, 0)

    return {
      total: items.length,
      damaged,
      lost,
      overdue,
      totalValue
    }
  }

  return {
    items,
    loading,
    error,
    refetch: fetchItems,
    getFilteredItems,
    getReportStats
  }
}