export interface Database {
  public: {
    Tables: {
      items: {
        Row: {
          id: string
          name: string
          category: string
          serial_number: string | null
          purchase_date: string | null
          purchase_price: number | null
          current_value: number | null
          location: string | null
          status: 'available' | 'checked_out' | 'damaged' | 'lost' | 'maintenance'
          condition: 'excellent' | 'good' | 'fair' | 'poor' | 'damaged'
          notes: string | null
          created_at: string
          updated_at: string
        }
        Insert: {
          id?: string
          name: string
          category: string
          serial_number?: string | null
          purchase_date?: string | null
          purchase_price?: number | null
          current_value?: number | null
          location?: string | null
          status?: 'available' | 'checked_out' | 'damaged' | 'lost' | 'maintenance'
          condition?: 'excellent' | 'good' | 'fair' | 'poor' | 'damaged'
          notes?: string | null
          created_at?: string
          updated_at?: string
        }
        Update: {
          id?: string
          name?: string
          category?: string
          serial_number?: string | null
          purchase_date?: string | null
          purchase_price?: number | null
          current_value?: number | null
          location?: string | null
          status?: 'available' | 'checked_out' | 'damaged' | 'lost' | 'maintenance'
          condition?: 'excellent' | 'good' | 'fair' | 'poor' | 'damaged'
          notes?: string | null
          created_at?: string
          updated_at?: string
        }
      }
      checkouts: {
        Row: {
          id: string
          item_id: string
          user_name: string
          user_email: string
          checkout_date: string
          due_date: string
          return_date: string | null
          status: 'active' | 'returned' | 'overdue'
          notes: string | null
          created_at: string
          updated_at: string
        }
        Insert: {
          id?: string
          item_id: string
          user_name: string
          user_email: string
          checkout_date: string
          due_date: string
          return_date?: string | null
          status?: 'active' | 'returned' | 'overdue'
          notes?: string | null
          created_at?: string
          updated_at?: string
        }
        Update: {
          id?: string
          item_id?: string
          user_name?: string
          user_email?: string
          checkout_date?: string
          due_date?: string
          return_date?: string | null
          status?: 'active' | 'returned' | 'overdue'
          notes?: string | null
          created_at?: string
          updated_at?: string
        }
      }
    }
    Views: {
      [_ in never]: never
    }
    Functions: {
      [_ in never]: never
    }
    Enums: {
      [_ in never]: never
    }
  }
}