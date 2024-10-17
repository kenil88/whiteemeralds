<ul>
    @foreach ($payments->payments as $payment)
        <li>
            @include('plugins/ccavenue::detail', compact('payment'))
        </li>
    @endforeach
</ul>
