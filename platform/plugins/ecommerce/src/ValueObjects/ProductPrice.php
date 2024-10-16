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
            $default_size = 13;
            $gemstone_price = 0;
            $stone_weight = '';
            $stone_type = '';
            $options = $options;
            $total_price_with_tax = 0;
            if ($this->product->categories[0]->id == 25) {
                $default_size = 20; // Reference size
                $selected_size = $default_size; // Initialize selected size
            }
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
                    if ($info === "Gem Stone" || $info === "Black Diamond") {
                        $gemstone_price = $options['options']['optionCartValue'][$key][0]['affect_price'];
                        $stone_type = $options['options']['optionCartValue'][$key][0]['option_value'];
                        $stone_weight = $options['options']['optionCartValue'][$key][0]['weight'];
                    }

                    if ($info === "Size") {
                        $selected_size = $options['options']['optionCartValue'][$key][0]['option_value'];
                        if ($this->product->categories[0]->id == 23 || $this->product->categories[0]->id == 24 || $this->product->categories[0]->id == 34) {
                            $size_difference = $selected_size - $default_size; // Calculate the size difference

                            // Adjust gold weight by 0.150 for each size difference
                            $weight_change = abs($size_difference) * 0.150;

                            if ($size_difference > 0) {
                                // Size increased, increase gold weight
                                $goldWeight += $weight_change;
                            } elseif ($size_difference < 0) {
                                // Size decreased, decrease gold weight
                                $goldWeight -= $weight_change;
                            }
                        } elseif ($this->product->categories[0]->id == 25) {
                            $size_difference = $selected_size - $default_size; // Calculate the size difference
                            // Adjust gold weight by 0.250 for each size difference
                            $weight_change = abs($size_difference) * 0.250;
                            if ($size_difference > 0) {
                                // Size increased, increase gold weight
                                $goldWeight += $weight_change;
                            } elseif ($size_difference < 0) {
                                // Size decreased, decrease gold weight
                                $goldWeight -= $weight_change;
                            }
                        }
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

                    if ($goldWeight <= 5) {
                        $making_charges *= 5;
                    } else {
                        $making_charges *= $goldWeight;
                    }

                    $final_price = $price + $making_charges + $certificate_charges + $diamond_price + $gemstone_price;
                    $tax = $final_price * 3 / 100;
                    $total_price_with_tax = round($final_price + $tax);
                } else {
                    $price = round($gold_price, 2);
                    $certificate_charges = (float) round(config('plugins.ecommerce.general.certificate_charge.Out_of_india'), 2);
                    $making_charges = (float) round(config('plugins.ecommerce.general.making_charge.Out_of_india'), 2);

                    if ($goldWeight <= 5) {
                        $making_charges *= 5;
                    } else {
                        $making_charges *= $goldWeight;
                    }

                    $final_price = $price + $making_charges + $certificate_charges + $diamond_price + $gemstone_price;
                    $total_price_with_tax = round($final_price, 2);
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

    public function getPriceforproductlisting($options = null, bool $includingTaxes = true): float
    {

        if ($this->product->categories[0]->parent_id == 35) {
            if ($includingTaxes) {
                if (get_application_currency_id() == 1) {
                    if ($this->product->categories[0]->id == 37) {
                        $price = config('plugins.ecommerce.general.925_price.ring');
                    } elseif ($this->product->categories[0]->id == 38) {
                        $price = config('plugins.ecommerce.general.925_price.set');
                    } elseif ($this->product->categories[0]->id == 39) {
                        $price = config('plugins.ecommerce.general.925_price.earring');
                    } elseif ($this->product->categories[0]->id == 36) {
                        $price = config('plugins.ecommerce.general.925_price.kada');
                    }
                } else {
                    $price = $this->product->front_sale_price_with_taxes != $this->product->price_with_taxes
                        ? $this->product->front_sale_price_with_taxes
                        : $this->product->price_with_taxes;
                }
            } else {
                $price = $this->product->isOnSale() ? $this->product->front_sale_price : $this->product->price;
            }

            return $this->applyFilters('price', 'value', (float) $price);
        } else {

            $gold_weight = Option::select('ec_option_value.weight')
                ->join('ec_option_value', 'ec_option_value.option_id', '=', 'ec_options.id')
                ->where('ec_options.product_id', $this->product->id)
                ->where(function ($query) {
                    if (get_application_currency_id() == 4) {
                        $query->where('ec_option_value.option_value', '14K');
                    } else {
                        $query->where('ec_option_value.option_value', '10K');
                    }
                })
                ->first();

            $diamond_weight = Option::select('ec_option_value.weight')
                ->join('ec_option_value', 'ec_option_value.option_id', 'ec_options.id')
                ->where('ec_options.product_id', $this->product->id)
                ->where('ec_option_value.option_value', 'Lab Grown Diamond')
                ->first();

            if (get_application_currency_id() == 4) {
                $labgrown_diamond_price_per_carat = config('plugins.ecommerce.general.diamond_charges.labgrown');
            } else {
                if (isset($diamond_weight->weight) && $diamond_weight->weight > 0) {

                    $labgrown_diamond_price_per_carat =  ($diamond_weight->weight > '0.20')
                        ? config('plugins.ecommerce.general.diamond_charges_USD.upto_20')
                        : config('plugins.ecommerce.general.diamond_charges_USD.after_20');
                } else {

                    $labgrown_diamond_price_per_carat = 0;
                }
            }

            $gemstone_price = Option::select('ec_option_value.affect_price')
                ->join('ec_option_value', 'ec_option_value.option_id', 'ec_options.id')
                ->where('ec_options.product_id', $this->product->id)
                ->whereIn('ec_option_value.option_value', ['Black', 'Sapphire', 'Yellow Sapphire', 'Ruby', 'Pearl', 'Emerald'])
                ->first();


            $gold_price = 0;
            if ($gold_weight && get_application_currency_id() == 4) {
                $certificate_charges = config('plugins.ecommerce.general.certificate_charge.India');
                $gold_price = $gold_weight->weight * config('plugins.ecommerce.general.gold_price.14K');
                $making_charges = config('plugins.ecommerce.general.making_charge.India');
                $tax_info = Tax::where('id', 4)->first();
                $tax_info = $tax_info->percentage;
            } else {
                $gold_price = $gold_weight->weight * config('plugins.ecommerce.general.gold_price.10K');
                $certificate_charges = (float) round(config('plugins.ecommerce.general.certificate_charge.Out_of_india'), 2);
                $making_charges = (float) round(config('plugins.ecommerce.general.making_charge.Out_of_india'), 2);
                $tax_info = 0;
            }

            if ($gold_weight) {
                if ($gold_weight->weight <= 5) {
                    $making_charges *= 5;
                } else {
                    $making_charges *= $gold_weight->weight;
                }
            }

            $price = round($gold_price, 2);
            if (isset($diamond_weight->weight) && $diamond_weight->weight > 0) {
                $diamond_price = $diamond_weight->weight * $labgrown_diamond_price_per_carat;
            } else {
                $diamond_price = 0;
            }

            if (isset($gemstone_price->affect_price) && $gemstone_price->affect_price != 'null') {
                $final_price = $price + $making_charges + $certificate_charges + $diamond_price + $gemstone_price->affect_price;
                // print_r($price);
                // print_r('     ');
                // print_r($making_charges);
                // print_r('     ');
                // print_r($certificate_charges);
                // print_r('     ');
                // print_r($diamond_price);
                // print_r('     ');
                // print_r($gold_weight->weight);
            } else {
                $final_price = $price + $making_charges + $certificate_charges + $diamond_price;
            }

            $tax = $final_price * $tax_info / 100;

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
    }

    public function displayAsTextProductListing(): string
    {
        $priceText = format_price(round($this->getPriceforproductlisting()));
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
