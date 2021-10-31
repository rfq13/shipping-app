@php
    $user = $billings['user'];
    $billings = $billings[0];
    // dd($billings['orders'][0]);
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Invoice</title>
</head>
<body>
    <span>Nama: {{ $user->name }}</span>
    <p>Email: {{ $user->email }}</p>
    <br>
    <table>
        <td>No.Billing: {{ $billings['code'] }}</td>
    </table>
    <br>
    <br>
    @foreach ($billings['orders'] as $order)
        <span>invoice : {{ $order['invoice'] }}</span>
        <br>
        <span>status pembayaran : {{ $order['payment_status'] }}</span>
        <br>
        <table>
            @foreach ($order['order_details'] as $detail)
            <tr>
                <td>Nama produk: produk {{ $detail['product_id'] }} spesial</td>
                <td>==>> Qty: {{ $detail['qty'] }}</td>
            </tr>
            @endforeach
        </table>
        <br>
        <hr>
    @endforeach
    
</body>
</html>