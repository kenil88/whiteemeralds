<?php

namespace Botble\Ecommerce\Http\Controllers\Fronts;

use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Facades\ProductCategoryHelper;
use Botble\Ecommerce\Http\Controllers\BaseController;
use Botble\Ecommerce\Models\Option;
use Botble\Ecommerce\Models\OptionValue;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\Tax;
use Botble\Ecommerce\Services\Products\GetProductService;
use Botble\Theme\Facades\Theme;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class PublicAjaxController extends BaseController
{
    public function ajaxSearchProducts(Request $request, GetProductService $productService)
    {
        $request->merge(['num' => 12]);

        $with = EcommerceHelper::withProductEagerLoadingRelations();

        $products = $productService->getProduct($request, null, null, $with);

        $queries = $request->input();

        foreach ($queries as $key => $query) {
            if (! $query || $key == 'num' || (is_array($query) && ! Arr::get($query, 0))) {
                unset($queries[$key]);
            }
        }

        $total = $products->count();

        return $this
            ->httpResponse()
            ->setData(view(EcommerceHelper::viewPath('includes.ajax-search-results'), compact('products', 'queries'))->render())
            ->setMessage($total != 1 ? __(':total Products found', compact('total')) : __(':total Product found', compact('total')));
    }

    public function ajaxGetCategoriesDropdown()
    {
        $categoriesDropdownView = Theme::getThemeNamespace('partials.product-categories-dropdown');

        return $this
            ->httpResponse()
            ->setData([
                'select' => ProductCategoryHelper::renderProductCategoriesSelect(),
                'dropdown' => view()->exists($categoriesDropdownView)
                    ? view($categoriesDropdownView)->render()
                    : null,
            ]);
    }

    public function  getProductPrice(Request $request)
    {
        $json = [];
        $ret = $this->getPrice($request);

        if (! empty($ret)) {
            $total_price_with_tax = format_price($ret['gold_price'] * $ret['gold_weight']);

            // dd($total_price_with_tax);

            $json['product_info'] = array_filter($ret['product_info'], function ($value, $key) {
                return in_array($key, ['length', 'wide', 'height']);
            }, ARRAY_FILTER_USE_BOTH);
            $json['metal_purity'] = $ret['metal_purity'];
            $json['gold_weight'] = round($ret['gold_weight'], 3);
            $json['gold_price'] = format_price_without_symbol($ret['gold_price']);
            $json['diamond_price'] = format_price_without_symbol($ret['diamond_price']);
            // $json['black_diamond_price'] = $this->currency->format($ret['black_diamond_price'], $this->session->data['currency']);
            $json['gemstone_price'] = format_price_without_symbol($ret['gemstone_price']);
            $json['certificate_charges'] = format_price_without_symbol($ret['certificate_charges']);
            $json['making_charges'] = format_price_without_symbol($ret['making_charges']);
            $json['product_price'] = format_price_without_symbol($ret['original_price']);
            // $json['product_price_with_option'] = $this->currency->format($ret['price'], $this->session->data['currency']);
            $json['tax'] = format_price_without_symbol($ret['tax']);
            $json['total_price_with_tax'] = format_price(round($ret['total']));
            if (get_application_currency_id() === 1) {
                $json['total_price_with_tax'] = 'USD ' . (round($ret['total'], 2));
            }
            $json['diamond_name'] = $ret['diamond_name'];
            $json['diamond_weight'] = $ret['diamond_weight'];
            $json['diamond_type'] = $ret['diamond_type'];
            $json['diamond_qty'] = $ret['diamond_qty'];
            $json['stone_type'] = $ret['stone_type'];
            $json['no_of_stone'] = $ret['no_of_stone'];
            $json['stone_weight'] = $ret['stone_weight'];
            $json['thikness'] = $ret['thikness'];
            $json['diameter'] = $ret['diameter'];
            // $json['total_price_without_tax'] = $this->currency->format($ret['total'], $this->session->data['currency']);
            // $json['current_theme'] = $this->config->get('theme_default_directory');

            return response()->json($json);
        }
    }

    private function getPrice($request)
    {
        $product_id = $request['product_id'];

        $product_info = Product::select('length', 'wide', 'height', 'tax_id', 'diamond_qty', 'gemstone_qty', 'thikness', 'diameter')->where('id', $product_id)->first();

        $get_cat_id = DB::table('ec_product_category_product')
            ->selectRaw('category_id')
            ->where('product_id', $product_id)
            ->first();
        $tax_info = null;
        if ($product_info) {
            $tax_info = Tax::where('id', $product_info->tax_id)->first();
        }

        $qty = $request['qty'];

        $black_diamond_price = 0;

        $gemstone_price = 0;

        $natural_diamond_price_per_carat = 3000; // Example price for natural diamond (per carat)

        $gold_price_14k_per_gram = config('plugins.ecommerce.general.gold_price.14K'); // Example price for 14k gold (per gram)
        $gold_price_18k_per_gram = config('plugins.ecommerce.general.gold_price.18K'); // Example price for 18k gold (per gram)

        $gold_weight = 0;
        $diamond_weight = 0;
        $diamond_type = '';
        $diamond_name = '';
        $gold_purity = '';
        $selected_size = 0;
        $default_size = 13;
        $stone_type = '';
        $stone_weight = 0;
        $diamond_final_type = '';
        $diamond_price = 0;

        if ($get_cat_id->category_id == 25) {
            $default_size = 20; // Reference size
            $selected_size = $default_size; // Initialize selected size
        }

        foreach ($request['options'] as $product_option_id => $value) {
            // Fetch options related to the product
            if (is_array($value) && isset($value['values'])) {
                $value = $value['values'];
            } elseif (is_string($value)) {
                $value = $value;
            }

            $option_query = Option::select(
                'ec_option_value.id as opv_id',
                'ec_options.name',
                'ec_option_value.affect_price',
                'ec_options.id as op_id',
                'ec_option_value.option_value',
                'ec_option_value.weight'
            )->join('ec_option_value', 'ec_option_value.option_id', 'ec_options.id')
                ->where('ec_options.product_id', $product_id)
                ->get();

            if ($option_query->count()) {
                foreach ($option_query as $option) {
                    $option_value = trim($option['option_value']);
                    $weight = $option['weight'];
                    // Check if the selected option is Natural or Lab-grown Diamond
                    if ($option['name'] == 'Diamond') {
                        if ($value == 'Natural Diamond' && $option_value == 'Natural Diamond') {
                            $diamond_price = $option['affect_price'];  // Set weight for natural diamond
                            $diamond_type = 'natural';  // Mark diamond type as natural
                        }
                        if ($option_value == 'Lab Grown Diamond') {
                            $labgrown_weight = $option['weight'];
                        }
                        if ($value == 'Lab Grown Diamond' && $option_value == 'Lab Grown Diamond') {
                            $diamond_weight = $weight;  // Set weight for lab-grown diamond
                            $diamond_type = 'labgrown'; // Mark diamond type as lab-grown
                        }
                    } elseif ($option['name'] == 'Gem Stone' || $option['name'] == 'Black Diamond') {
                        $gemstone_price = $option['affect_price'];
                        $stone_type = $option['option_value'];
                        $stone_weight = $option['weight'];
                    }
                    // Check if the selected option is 14K or 18K gold
                    if ($value == '14K' && $option_value == '14K') {
                        $gold_weight = $weight;  // Set weight for 14k gold
                        $metal_purity = '14k';  // Mark gold purity as 14k
                    } elseif ($value == '18K' && $option_value == '18K') {
                        $gold_weight = $weight;  // Set weight for 18k gold
                        $metal_purity = '18k';  // Mark gold purity as 18k
                    } elseif ($value == '10K' && $option_value == '10K') {
                        $gold_weight = $weight;  // Set weight for 18k gold
                        $metal_purity = '10k';  // Mark gold purity as 18k
                    }

                    if (is_numeric($value)) {
                        $selected_size = (int) $value;
                    }
                }
            }
        }

        // category id = 25 is for ladies
        // category id = 24 is for gentes
        if ($get_cat_id->category_id == 23 || $get_cat_id->category_id == 24 || $get_cat_id->category_id == 34) {
            $size_difference = $selected_size - $default_size; // Calculate the size difference

            // Adjust gold weight by 0.150 for each size difference
            $weight_change = abs($size_difference) * 0.150;

            if ($size_difference > 0) {
                // Size increased, increase gold weight
                $gold_weight += $weight_change;
            } elseif ($size_difference < 0) {
                // Size decreased, decrease gold weight
                $gold_weight -= $weight_change;
            }
        } elseif ($get_cat_id->category_id == 25) {
            $size_difference = $selected_size - $default_size; // Calculate the size difference

            // Adjust gold weight by 0.250 for each size difference
            $weight_change = abs($size_difference) * 0.250;
            if ($size_difference > 0) {
                // Size increased, increase gold weight
                $gold_weight += $weight_change;
            } elseif ($size_difference < 0) {
                // Size decreased, decrease gold weight
                $gold_weight -= $weight_change;
            }
        }


        // Calculate price based on selected options

        if ($diamond_type == 'natural') {
            $diamond_price = $diamond_price;
            $diamond_name = 'Natural Diamond';
            $diamond_weight = $labgrown_weight;
            $diamond_final_type = 'EF Vvs';
            if (get_application_currency_id() == 1) {
                $diamond_final_type = 'EF Vvs / vs';
            }
        }
        if ($diamond_type == 'labgrown') {
            $diamond_name = 'Lab Grown Diamond';
            if (get_application_currency_id() == 4) {
                $labgrown_diamond_price_per_carat = config('plugins.ecommerce.general.diamond_charges.labgrown'); // Example price for lab-grown diamond (per carat)
            } else {
                if ($diamond_weight > '0.20') {
                    $labgrown_diamond_price_per_carat = config('plugins.ecommerce.general.diamond_charges_USD.upto_20'); // Example price for lab-grown diamond (per carat)
                } else {
                    $labgrown_diamond_price_per_carat = config('plugins.ecommerce.general.diamond_charges_USD.after_20'); // Example price for lab-grown diamond (per carat)
                }
            }
            $diamond_price = $diamond_weight * $labgrown_diamond_price_per_carat;
            $diamond_final_type = 'EF Vvs / VS';
            if (get_application_currency_id() == 1) {
                $diamond_final_type = 'HI SI';
            }
        }

        $gemstone_price = $gemstone_price;

        $stone_type = $stone_type;

        $stone_weight = $stone_weight;

        $gold_price = 0;
        if ($metal_purity == '14k') {
            if ($get_cat_id->category_id == 23 || $get_cat_id->category_id == 24 || $get_cat_id->category_id == 34) {
                $gold_price = $gold_weight * config('plugins.ecommerce.general.gold_price.14K'); // Example price for 14k gold (per gram)
            } elseif ($get_cat_id->category_id == 25) {
                $gold_price = $gold_weight * config('plugins.ecommerce.general.gold_price.14K'); // Example price for 14k gold (per gram)
            } else {
                $gold_price = $gold_weight * config('plugins.ecommerce.general.gold_price.14K'); // Example price for 14k gold (per gram)
            }
        } elseif ($metal_purity == '18k') {
            if ($get_cat_id->category_id == 23 || $get_cat_id->category_id == 24 || $get_cat_id->category_id == 34) {
                $gold_price = $gold_weight * config('plugins.ecommerce.general.gold_price.18K'); // Example price for 18k gold (per gram)
            } elseif ($get_cat_id->category_id == 25) {
                $gold_price = $gold_weight * config('plugins.ecommerce.general.gold_price.18K'); // Example price for 18k gold (per gram)
            } else {
                $gold_price = $gold_weight * config('plugins.ecommerce.general.gold_price.18K'); // Example price for 18k gold (per gram)
            }
        } elseif ($metal_purity == '10k') {
            if ($get_cat_id->category_id == 23 || $get_cat_id->category_id == 24 || $get_cat_id->category_id == 34) {
                $gold_price = $gold_weight * config('plugins.ecommerce.general.gold_price.10K'); // Example price for 10k gold (per gram)
            } elseif ($get_cat_id->category_id == 25) {
                $gold_price = $gold_weight * config('plugins.ecommerce.general.gold_price.10K'); // Example price for 10k gold (per gram)
            } else {
                $gold_price = $gold_weight * config('plugins.ecommerce.general.gold_price.10K'); // Example price for 10k gold (per gram)
            }
        }

        if (get_application_currency_id() == 4) {
            $price = round($gold_price, 2);
            $gold_price = $gold_price;
            $diamond_price = $diamond_price;
            $certificate_charges = (float) config('plugins.ecommerce.general.certificate_charge.India');
            $making_charges = (float) config('plugins.ecommerce.general.making_charge.India');


            if ($gold_weight <= 5) {
                $making_charges *= 5;
            } else {
                $making_charges *= $gold_weight;
            }

            $final_price = $price + $making_charges + $certificate_charges + $diamond_price + $gemstone_price;

            $tax = $final_price * $tax_info->percentage / 100;

            $total_price_with_tax = $tax + $final_price;

            $total_price_with_tax = round($total_price_with_tax, 2);
        } else {

            $price = round($gold_price / get_current_exchange_rate(), 2);
            $gold_price = round($gold_price / get_current_exchange_rate(), 2);
            $diamond_price = round($diamond_price / get_current_exchange_rate(), 2);
            $certificate_charges = (float) round(config('plugins.ecommerce.general.certificate_charge.Out_of_india') / get_current_exchange_rate(), 2);
            $making_charges = (float) round(config('plugins.ecommerce.general.making_charge.Out_of_india') / get_current_exchange_rate(), 2);

            if ($gold_weight <= 5) {
                $making_charges *= 5;
            } else {
                $making_charges *= $gold_weight;
            }
            $final_price = $price + $making_charges + $certificate_charges + $diamond_price + $gemstone_price;

            $tax = 0;
            $total_price_with_tax = $tax + $final_price;
            $total_price_with_tax = round($total_price_with_tax, 2);
        }


        // Output total price and individual prices for debugging
        $arr =
            [
                'product_info' => ['length' => $product_info->length, 'wide' => $product_info->wide, 'height' => $product_info->height],

                'metal_purity' => $metal_purity,

                'diamond_name' => $diamond_name,

                'gold_weight' => $gold_weight,

                'gold_price' => $gold_price,

                'diamond_weight' => $diamond_weight,

                'diamond_price' => $diamond_price,

                'black_diamond_price' => $black_diamond_price,

                'gemstone_price' => $gemstone_price,

                'certificate_charges' => $certificate_charges,

                'making_charges' => $making_charges,

                'original_price' => $price,

                'tax' => $tax,

                'price' => $price,

                'total' => $total_price_with_tax * $qty,

                'diamond_type' => $diamond_final_type,

                'diamond_qty' => $product_info->diamond_qty,

                'stone_type' => $stone_type,

                'no_of_stone' => $product_info->gemstone_qty,

                'stone_weight' => $stone_weight,

                'diameter' => $product_info->diameter,

                'thikness' => $product_info->thikness
            ];
        return $arr;
    }
}
