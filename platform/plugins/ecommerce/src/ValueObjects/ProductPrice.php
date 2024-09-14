<?php

namespace Botble\Ecommerce\ValueObjects;

use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Models\Option;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\ProductVariation;
use Botble\Ecommerce\Models\Tax;
use Illuminate\Database\Eloquent\Collection;

/**
 * @phpstan-consistent-constructor
 */
class ProductPrice
{
    protected mixed $state;

    protected Collection $variations;

    protected ?Product $minimumVariation;

    protected ?Product $maximumVariation;

    public function __construct(protected Product $product) {}

    public static function make(Product $product): static
    {
        return new static($product);
    }

    public function getPrice(bool $includingTaxes = true): float
    {

        $lab_grown_price = config('plugins.ecommerce.general.diamond_charges.labgrown');

        $gold_weight = Option::select('ec_option_value.weight')->join('ec_option_value', 'ec_option_value.option_id', 'ec_options.id')->where('ec_options.product_id', $this->product->id)->where('ec_option_value.option_value', '14K')->first();

        $diamond_weight = Option::select('ec_option_value.weight')->join('ec_option_value', 'ec_option_value.option_id', 'ec_options.id')->where('ec_options.product_id', $this->product->id)->where('ec_option_value.option_value', 'Lab Grown Diamond')->first();

        $tax_info = Tax::where('id', 4)->first();
        $gold_price = 0;
        if ($gold_weight) {

            $gold_price = $gold_weight->weight * config('plugins.ecommerce.general.gold_price.14K');
        }

        $certificate_charges = config('plugins.ecommerce.general.certificate_charge.India');

        $making_charges = config('plugins.ecommerce.general.making_charge.India');

        if ($gold_weight) {
            if ($gold_weight->weight <= 5) {

                $making_charges *= 5;
            } else {

                $making_charges *= $gold_weight->weight;
            }
        }

        $price = round($gold_price, 2);

        if (isset($lab_grown_price->weight) && $lab_grown_price->weight > 0) {

            $diamond_price = $diamond_weight->weight * $lab_grown_price;
        } else {

            $diamond_price = 0;
        }

        $final_price = $price + $making_charges + $certificate_charges + $diamond_price;

        $tax = $final_price * $tax_info->percentage / 100;

        $total_price_with_tax = $tax + $final_price;

        // if ($includingTaxes) {
        //     $price = $this->product->front_sale_price_with_taxes != $this->product->price_with_taxes
        //         ? ($total_price_with_tax ?? 0)  // If affect_price is null, set it to 0
        //         : ($total_price_with_tax ?? 0);
        // } else {
        //     $price = $this->product->isOnSale() ? $this->product->front_sale_price : $this->product->id;
        // }

        return $this->applyFilters('price', 'value', (float) $total_price_with_tax);
    }

    public function displayAsText(): string
    {
        $priceText = format_price($this->getPrice());

        return $this->applyFilters('price', 'display_as_text', $priceText);
    }

    public function displayAsHtml(...$args): string
    {
        return view(EcommerceHelper::viewPath('products.partials.price'), [
            'product' => $this->product,
            ...$args,
        ])->render();
    }

    public function getPriceOriginal(): float
    {
        return $this->applyFilters(
            'price_original',
            'value',
            (float) $this->product->price_with_taxes
        );
    }

    public function displayPriceOriginalAsText(): string
    {
        $priceText = format_price($this->getPriceOriginal());

        return $this->applyFilters('original_price', 'display_as_text', $priceText);
    }

    public function getPriceMinimum(): float
    {
        $minimumVariation = $this->getMinimumVariation();

        return $minimumVariation
            ? $minimumVariation->price()->getPrice()
            : $this->getPrice();
    }

    public function displayPriceMinimumAsText(): string
    {
        return format_price($this->getPriceMinimum());
    }

    public function getPriceMaximum(): float
    {
        $maximumVariation = $this->getMaximumVariation();

        return $maximumVariation
            ? $maximumVariation->price()->getPrice()
            : $this->getPrice();
    }

    public function displayPriceMaximumAsText(): string
    {
        return format_price($this->getPriceMaximum());
    }

    protected function getVariations(): Collection
    {
        $this->product->loadMissing('variations.product');

        return $this->variations ??= $this->product->variations;
    }

    protected function getMinimumVariation(): ?Product
    {
        return $this->minimumVariation ??= $this
            ->getVariations()
            ->sortBy(function (ProductVariation $productVariation) { // @phpstan-ignore-line
                return $productVariation->product->price()->getPrice();
            })
            ->first()
            ?->product;
    }

    protected function getMaximumVariation(): ?Product
    {
        return $this->maximumVariation ??= $this
            ->getVariations()
            ->sortByDesc(function (ProductVariation $productVariation) { // @phpstan-ignore-line
                return $productVariation->product->price()->getPrice();
            })
            ->first()
            ?->product;
    }

    protected function applyFilters(string $name, string $kind, mixed $value): mixed
    {
        return apply_filters(
            "product_prices_{$name}_$kind",
            $value,
            $this->product
        );
    }
}
