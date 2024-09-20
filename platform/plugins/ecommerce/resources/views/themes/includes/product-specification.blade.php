<div class="row justify-content-center">
    <div class="col-xl-10">
        {{-- <table>
            <tbody>
            @foreach($product->specificationAttributes->where('pivot.hidden', false)->sortBy('pivot.order') as $attribute)
                <tr>
                    <td>{{ $attribute->name }}</td>
                    <td>
                        @if ($attribute->type === 'checkbox')
                            @if ($attribute->pivot->value)
                                <x-core::icon name="ti ti-check" class="text-success" style="font-size: 1.5rem;" />
                            @else
                                <x-core::icon name="ti ti-x" class="text-danger" style="font-size: 1.5rem;" />
                            @endif
                        @else
                            {{ $attribute->pivot->value }}
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table> --}}

        <table class="table table-bordered">
    <thead>
        <tr>
            <td colspan="2"><strong>Gold Details</strong></td>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Gold Weight</td>
            <td id="GoldWeight"></td>
        </tr>
    </tbody>
    <thead>
        <tr>
            <td colspan="2"><strong>Metal Details</strong></td>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Width</td>
            <td id="Width"></td>
        </tr>
        <tr>
            <td>Height</td>
            <td id="Height"></td>
        </tr>
        <tr>
            <td>Length</td>
            <td id="Length"></td>
        </tr>
    </tbody>
    <thead id="DiamondNameRow">
        <tr>
            <td colspan="2"><strong id="DiamondName"></strong></td>
        </tr>
    </thead>
    <tbody id="DiamondTypeRow">
        <tr>
            <td>Diamond Type</td>
            <td id="DiamondType"></td>
        </tr>
        <tr>
            <td>Total No of Diamonds</td>
            <td id="TotalNoofDiamonds">
            </td>
        </tr>
        <tr>
            <td>Diamond Weight</td>
            <td id="DiamondWeight">
            </td>
        </tr>
    </tbody>
    <thead id="DiamondNameRow">
        <tr>
            <td colspan="2"><strong id="DiamondName">Gemstone Details</strong></td>
        </tr>
    </thead>
    <tbody id="DiamondTypeRow">
        <tr>
            <td>Gemstone Type</td>
            <td id="StoneType"></td>
        </tr>
        <tr>
            <td>Total No of Gemstones</td>
            <td id="TotalNoofGemtones">
            </td>
        </tr>
        <tr>
            <td>Gemstone Weight [Approx]</td>
            <td id="GemstoneWeight">
            </td>
        </tr>
    </tbody>
    <thead>
        <tr>
            <td colspan="2"><strong>Price Breakup</strong></td>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Gold Charges</td>
            <td id="GoldCharges"></td>
        </tr>
        <tr>
            <td id="DiamondChargesName">Diamond Charges</td>
            <td id="DiamondCharges"></td>
        </tr>
        <tr>
            <td>Certification Charges</td>
            <td id="CertificationCharges"></td>
        </tr>
        <tr>
            <td>Making Charge</td>
            <td id="LabourCost"></td>
        </tr>
        <tr>
            <td>GST</td>
            <td id="GST"></td>
        </tr>
        <tr>
            <td>Total Price</td>
            <td id="TotalPrice"></td>
        </tr>
    </tbody>
</table>
    </div>
</div>
