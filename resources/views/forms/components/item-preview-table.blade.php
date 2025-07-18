@if (!empty($items) && count($items))
    <table>
        <thead>
            <tr>
                <th>Item ID</th>
                <th>Quantity</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($items as $item)
                <tr>
                    <td>{{ $item['item_id'] ?? 'N/A' }}</td>
                    <td>{{ $item['pivot']['quantity'] ?? '0' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@else
    <p>No items found.</p>
@endif