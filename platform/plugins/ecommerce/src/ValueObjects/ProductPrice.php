<?php

namespace Botble\Ecommerce\ValueObjects;

use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Models\Option;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\ProductVariation;
use Botble\Ecommerce\Models\Tax;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Session;

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

    public function getPriceCart($options, bool $includingTaxes = true): float
    {
        if ($this->product->categories[0]->parent_id == 35) {
            if ($includingTaxes) {
                $price = $this->product->front_sale_price_with_taxes != $this->product->price_with_taxes
                    ? $this->product->front_sale_price_with_taxes
                    : $this->product->price_with_taxes;
            } else {
                $price = $this->product->isOnSale() ? $this->product->front_sale_price : $this->product->price;
            }

            return $this->applyFilters('price', 'value', (float) $price);
        } else {
            $looseBraceletSize = null;
            $diamondWeight = null;
            $goldWeight = null;
            $diamond_price = 0;
            $gemstone_price = 0;
            $stone_weight = '';
            $stone_type = '';
            $options = $options;
            $total_price_with_tax = 0;
            if ($options) {
                foreach ($options['options']['optionInfo'] as $key => $info) {
                    if ($info === "Loose Bracelet Size" && isset($options['options']['optionCartValue'][$key])) {
                        $looseBraceletSize = $options['options']['optionCartValue'][$key][0]['option_value'];
                    }

                    if (($info === "Diamond" || $info === "Lab Grown Diamond" || $info === "Natural Diamond")
                        && isset($options['options']['optionCartValue'][$key])
                    ) {
                        $diamond_option_value = $options['options']['optionCartValue'][$key][0]['option_value'];
                        $diamondWeight = $options['options']['optionCartValue'][$key][0]['weight'];

                        if ($diamond_option_value == 'Natural Diamond') {
                            $diamond_price = $options['options']['optionCartValue'][$key][0]['affect_price'];
                        }
                    }

                    if (($info === "Metal Purity") && isset($options['options']['optionCartValue'][$key])) {
                        $metal_purity = $options['options']['optionCartValue'][$key][0]['option_value'];
                        $goldWeight = $options['options']['optionCartValue'][$key][0]['weight'];
                    }

                    if ($info === "Gem Stone") {
                        $gemstone_price = $options['options']['optionCartValue'][$key][0]['affect_price'];
                        $stone_type = $options['options']['optionCartValue'][$key][0]['option_value'];
                        $stone_weight = $options['options']['optionCartValue'][$key][0]['weight'];
                    }
                }

                // Diamond Pricing Logic
                if ($diamond_option_value == 'Lab Grown Diamond') {
                    if (get_application_currency_id() == 4) {
                        $labgrown_diamond_price_per_carat = config('plugins.ecommerce.general.diamond_charges.labgrown');
                    } else {
                        $labgrown_diamond_price_per_carat = ($diamondWeight > '0.20')
                            ? config('plugins.ecommerce.general.diamond_charges_USD.upto_20')
                            : config('plugins.ecommerce.general.diamond_charges_USD.after_20');
                    }
                    $diamond_price = $diamondWeight * $labgrown_diamond_price_per_carat;
                }

                // Gold Pricing Logic
                $gold_price = 0;
                if ($metal_purity == '14K') {
                    $gold_price = $goldWeight * config('plugins.ecommerce.general.gold_price.14K');
                } elseif ($metal_purity == '18K') {
                    $gold_price = $goldWeight * config('plugins.ecommerce.general.gold_price.18K');
                } elseif ($metal_purity == '10K') {
                    $gold_price = $goldWeight * config('plugins.ecommerce.general.gold_price.10K');
                }

                if (get_application_currency_id() == 4) {
                    $price = round($gold_price, 2);
                    $certificate_charges = (float) config('plugins.ecommerce.general.certificate_charge.India');
                    $making_charges = (float) config('plugins.ecommerce.general.making_charge.India');
                    $final_price = $price + $making_charges + $certificate_charges + $diamond_price + $gemstone_price;
                    $tax = $final_price * 3 / 100;
                    $total_price_with_tax = round($final_price + $tax);
                } else {
                    $price = round($gold_price / get_current_exchange_rate(), 2);
                    $certificate_charges = (float) round(config('plugins.ecommerce.general.certificate_charge.Out_of_india') / get_current_exchange_rate(), 2);
                    $making_charges = (float) round(config('plugins.ecommerce.general.making_charge.Out_of_india') / get_current_exchange_rate(), 2);
                    $final_price = $price + $making_charges + $certificate_charges + $diamond_price + $gemstone_price;
                    $total_price_with_tax = round($final_price, 2);
                }

                if ($goldWeight <= 5) {
                    $making_charges *= 5;
                } else {
                    $making_charges *= $goldWeight;
                }
            }


            return $this->applyFilters('price', 'value', (float) $total_price_with_tax);
        }
    }

    public function displayAsTextCart($options): string
    {
        if (get_application_currency_id() == 4) {
            $priceText = format_price(round($this->getPriceCart($options)));
        } else {
            $priceText = format_price(round($this->getPriceCart($options), 2));
        }

        return $this->applyFilters('price', 'display_as_text', $priceText);
    }

    public function getPrice(bool $includingTaxes = true): float
    {
        if ($includingTaxes) {
            $price = $this->product->front_sale_price_with_taxes != $this->product->price_with_taxes
                ? $this->product->front_sale_price_with_taxes
                : $this->product->price_with_taxes;
        } else {
            $price = $this->product->isOnSale() ? $this->product->front_sale_price : $this->product->price;
        }

        return $this->applyFilters('price', 'value', (float) $price);
    }

    public function displayAsText(): string
    {
        $priceText = format_price(round($this->getPrice()));
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
