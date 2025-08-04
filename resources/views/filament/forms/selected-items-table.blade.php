@php
    $resolvedItems = is_callable($items) ? $items() : $items;
@endphp

@if(count($resolvedItems))
    <div class="overflow-x-auto rounded border border-gray-200 shadow-sm mt-4">
        <table class="min-w-full text-sm text-left">
            <thead class="bg-gray-100 text-xs font-semibold text-gray-700">
                <tr>
                    <th class="px-4 py-2">Item</th>
                    <th class="px-4 py-2">Quantity</th>
                </tr>
            </thead>
            <tbody>
                @foreach($resolvedItems as $item)
                    <tr class="border-t">
                        <td class="px-4 py-2">{{ \App\Models\Item::find($item['item_id'])->name ?? 'Unknown' }}</td>
                        <td class="px-4 py-2">{{ $item['pivot']['quantity'] ?? '' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
