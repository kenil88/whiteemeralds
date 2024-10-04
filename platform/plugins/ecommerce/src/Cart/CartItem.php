<?php

namespace Botble\Ecommerce\Cart;

use Botble\Ecommerce\Cart\Contracts\Buyable;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * @property string $created_at
 * @property string $updated_at
 * @property float $priceTax
 * @property-read float $subtotal
 * @property-read float $total
 * @property-read float $tax
 * @property-read float $taxTotal
 */
class CartItem implements Arrayable, Jsonable
{
    public string $rowId;

    public int|string|null $id;

    public int|float $qty;

    public string $name;

    public float $price;

    public array|Collection $options;

    protected ?string $associatedModel = null;

    protected float $taxRate = 0;

    public function __construct(int|string|null $id, ?string $name, float $price, array $options = [])
    {
        if (empty($id)) {
            throw new InvalidArgumentException('Please supply a valid identifier.');
        }

        if (empty($name)) {
            throw new InvalidArgumentException('Please supply a valid name.');
        }

        $this->id = $id;
        $this->name = $name;
        $this->options = new CartItemOptions($options);
        $this->rowId = $this->generateRowId($id, $options);
        $this->price = $this->calculatePrice();
        $this->created_at = Carbon::now();
        $this->updated_at = Carbon::now();
    }

    public function price(): string
    {
        return format_price($this->price);
    }

    public function priceTax(): string
    {
        return format_price($this->priceTax);
    }

    public function subtotal(): string
    {
        return format_price($this->subtotal);
    }

    public function total(): string
    {
        return format_price($this->total);
    }

    public function tax(): string
    {
        return format_price($this->tax);
    }

    public function taxTotal(): string
    {
        return format_price($this->taxTotal);
    }

    public function setQuantity(int|float $qty): void
    {
        if (empty($qty) || ! is_numeric($qty)) {
            throw new InvalidArgumentException('Please supply a valid quantity.');
        }

        $this->qty = $qty;
    }

    public function updateFromBuyable(Buyable $item): void
    {
        $this->id = $item->getBuyableIdentifier($this->options);
        $this->name = $item->getBuyableDescription($this->options);
        $this->price = $item->getBuyablePrice($this->options);
        $this->priceTax = $this->price;
    }

    public function updateFromArray(array $attributes): void
    {
        $this->id = Arr::get($attributes, 'id', $this->id);
        $this->qty = Arr::get($attributes, 'qty', $this->qty);
        $this->name = Arr::get($attributes, 'name', $this->name);
        $this->price = Arr::get($attributes, 'price', $this->price);
        $this->priceTax = $this->price;
        $this->options = new CartItemOptions(Arr::get($attributes, 'options', $this->options));

        $this->rowId = $this->generateRowId($this->id, $this->options->all());
    }

    public function associate($model): static
    {
        $this->associatedModel = is_string($model) ? $model : get_class($model);

        return $this;
    }

    public function setTaxRate(float $taxRate): static
    {
        $this->taxRate = $taxRate;

        return $this;
    }

    public function getTaxRate(): float
    {
        return $this->taxRate;
    }

    public function __get($attribute)
    {
        if (property_exists($this, $attribute)) {
            return $this->{$attribute};
        }

        if ($attribute === 'priceTax') {
            if (! EcommerceHelper::isTaxEnabled()) {
                return 0;
            }

            return $this->price + $this->tax;
        }

        if ($attribute === 'subtotal') {
            return $this->qty * $this->price;
        }

        if ($attribute === 'total') {
            return $this->qty * $this->price + $this->tax;
        }

        if ($attribute === 'tax') {
            if (! EcommerceHelper::isTaxEnabled()) {
                return 0;
            }

            return $this->price * ($this->taxRate / 100);
        }

        if ($attribute === 'taxTotal') {
            if (! EcommerceHelper::isTaxEnabled()) {
                return 0;
            }

            return $this->tax * $this->qty;
        }

        if ($attribute === 'model') {
            return (new $this->associatedModel())->find($this->id);
        }

        return null;
    }

    public static function fromBuyable(Buyable $item, array $options = []): self
    {
        return new self(
            $item->getBuyableIdentifier($options),
            $item->getBuyableDescription($options),
            $item->getBuyablePrice($options),
            $options
        );
    }

    public static function fromArray(array $attributes): self
    {
        $options = Arr::get($attributes, 'options', []);

        return new self($attributes['id'], $attributes['name'], $attributes['price'], $options);
    }

    public static function fromAttributes(int|string|null $id, string $name, float $price, array $options = []): self
    {
        return new self($id, $name, $price, $options);
    }

    protected function generateRowId(int|string|null $id, array $options): string
    {
        ksort($options);

        return md5($id . serialize($options));
    }

    public function toArray(): array
    {
        $this->price = $this->calculatePrice();

        return [
            'rowId' => $this->rowId,
            'id' => $this->id,
            'name' => $this->name,
            'qty' => $this->qty,
            'price' => $this->price,
            'options' => $this->options->toArray(),
            'tax' => $this->tax,
            'subtotal' => $this->subtotal,
            'updated_at' => $this->updated_at,
        ];
    }


    public function calculatePrice(): float
    {
        $options = $this->options->toArray();

        $get_cat_id = DB::select("select * from `ec_product_category_product` where `product_id` = '" . $this->id . "' limit 1");

        $cat_id = $get_cat_id[0]->category_id;

        $looseBraceletSize = null;
        $diamondWeight = null;
        $goldWeight = null;
        $diamond_price = 0;
        $default_size = 13;
        $gemstone_price = 0;
        $stone_weight = '';
        $stone_type = '';

        if ($cat_id == 25) {
            $default_size = 20; // Reference size
            $selected_size = $default_size; // Initialize selected size
        }

        if (isset($options['options'])) {
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
                    if ($cat_id == 23 || $cat_id == 24 || $cat_id == 34) {
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
                    } elseif ($cat_id == 25) {
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

            return $total_price_with_tax;
        } else {
            return 0;
        }
    }


    public function toJson($options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }
}
