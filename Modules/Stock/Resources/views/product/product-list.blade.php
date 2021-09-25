@php
    $grand_total = 0;
@endphp
@if (!$categories->isEmpty())
    @foreach ($categories as $index => $category)
        @if (!$category->warehouse_products->isEmpty())
            <div class="col-md-12 text-center"><h3 class="py-3 bg-warning text-white" style="max-width:300px;margin: 50px auto 10px auto;">{{ ($index+1).' : '.$category->name }}</h3></div>
            @php 
            $total_stock_value = 0; 
            @endphp
                
            <table id="dataTable" class="table table-bordered table-hover mb-5">
                <thead class="bg-primary">
                    <tr>
                        <th>Sl</th>
                        <th class="text-center">FG Code</th>
                        <th>FG Name</th>
                        <th class="text-center">Stock Unit</th>
                        <th class="text-right">Per Unit Price</th>
                        <th class="text-center">Stock Qty</th>
                        <th class="text-right">Stock Value</th>
                    </tr>
                </thead>
                <tbody>
                    @php $total = 0; @endphp
                    @if ($product_id)
                    @foreach ($category->warehouse_products as $key => $item)
                    @if ($product_id == $item->product_id && $warehouse_id == $item->warehouse_id)
                    <tr>
                        <td>{{ $key+1 }}</td>
                        <td class="text-center">{{ $item->product_id }}</td>
                        <td>{{ $item->product->name }}</td>
                        <td class="text-center">{{ $item->product->unit->unit_name }}</td>
                        <td class="text-right">{{ number_format($item->product->price,4,'.','') }}</td>
                        <td class="text-center">{{ $item->qty }}</td>
                        <td class="text-right">{{ number_format(($item->qty * $item->product->price),4,'.','') }}</td>
                        @php
                            $total += ($item->qty * $item->product->price);
                        @endphp
                    </tr>
                    @endif
                @endforeach
                    @else
                    @foreach ($category->warehouse_products as $key => $item)
                    @if($warehouse_id == $item->warehouse_id)
                    <tr>
                        <td>{{ $key+1 }}</td>
                        <td class="text-center">{{ $item->product_id }}</td>
                        <td>{{ $item->product->name }}</td>
                        <td class="text-center">{{ $item->product->unit->unit_name }}</td>
                        <td class="text-right">{{ number_format($item->product->price,4,'.','') }}</td>
                        <td class="text-center">{{ $item->qty }}</td>
                        <td class="text-right">{{ number_format(($item->qty * $item->product->price),4,'.','') }}</td>
                        @php
                            $total += ($item->qty * $item->product->price);
                        @endphp
                    </tr>
                    @endif
                @endforeach
                    @endif
                </tbody>
                <tfoot>
                    <tr class="bg-primary">
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th style="text-align: right !important;font-weight:bold;color:white;">Total</th>
                        <th style="text-align: right !important;font-weight:bold;color:white;">{{ number_format($total,4,'.','') }}</th>
                        @php
                        $total_stock_value += $total;
                    @endphp
                    </tr>
                </tfoot>
            </table>
            @php
                $grand_total += $total_stock_value;
            @endphp
        @endif
    @endforeach
    <h3 class="bg-dark text-white font-weight-bolder p-3 text-right">Grand Total = {{ number_format($grand_total,4,'.','') }}</h3>
@else 
    <div class="col-md-12 text-center"><h3 class="py-3 bg-danger text-white">Stock Data is Empty</h3></div>
@endif