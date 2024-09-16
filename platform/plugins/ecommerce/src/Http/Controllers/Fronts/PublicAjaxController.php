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
            $json['gold_price'] = format_price($ret['gold_price']);
            $json['diamond_price'] = format_price($ret['diamond_price']);
            // $json['black_diamond_price'] = $this->currency->format($ret['black_diamond_price'], $this->session->data['currency']);
            // $json['gemstone_price'] = $this->currency->format($ret['gemstone_price'], $this->session->data['currency']);
            $json['certificate_charges'] = format_price($ret['certificate_charges']);
            $json['making_charges'] = format_price($ret['making_charges']);
            $json['product_price'] = format_price($ret['original_price']);
            // $json['product_price_with_option'] = $this->currency->format($ret['price'], $this->session->data['currency']);
            $json['tax'] = format_price($ret['tax']);
            $json['total_price_with_tax'] = format_price(round($ret['total']));
            $json['diamond_name'] = $ret['diamond_name'];
            $json['diamond_weight'] = $ret['diamond_weight'];
            // $json['total_price_without_tax'] = $this->currency->format($ret['total'], $this->session->data['currency']);
            // $json['current_theme'] = $this->config->get('theme_default_directory');

            return response()->json($json);
        }
    }

    private function getPrice($request)
    {
        $product_id = $request['product_id'];

        $product_info = Product::select('length', 'wide', 'height', 'tax_id')->where('id', $product_id)->first();

        $tax_info = Tax::where('id', $product_info->tax_id)->first();

        $qty = $request['qty'];

        $black_diamond_price = 0;

        $gemstone_price = 0;

        $natural_diamond_price_per_carat = 3000; // Example price for natural diamond (per carat)
        $labgrown_diamond_price_per_carat = config('plugins.ecommerce.general.diamond_charges.labgrown'); // Example price for lab-grown diamond (per carat)
        $gold_price_14k_per_gram = config('plugins.ecommerce.general.gold_price.14K'); // Example price for 14k gold (per gram)
        $gold_price_18k_per_gram = config('plugins.ecommerce.general.gold_price.18K'); // Example price for 18k gold (per gram)

        $gold_weight = 0;
        $diamond_weight = 0;
        $diamond_type = '';
        $gold_purity = '';
        $default_size = 13; // Reference size
        // dd($request['options']);
        foreach ($request['options'] as $product_option_id => $value) {
            // Fetch options related to the product


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
                    if ($value == 'Natural Diamond' && $option_value == 'Natural Diamond') {
                        $diamond_price = $option['affect_price'];  // Set weight for natural diamond
                        $diamond_type = 'natural';  // Mark diamond type as natural

                    }

                    if ($value == 'Lab Grown Diamond' && $option_value == 'Lab Grown Diamond') {
                        $diamond_weight = $weight;  // Set weight for lab-grown diamond
                        $diamond_type = 'labgrown'; // Mark diamond type as lab-grown

                    }

                    // Check if the selected option is 14K or 18K gold
                    if ($value == '14K' && $option_value == '14K') {
                        $gold_weight = $weight;  // Set weight for 14k gold
                        $metal_purity = '14k';  // Mark gold purity as 14k
                    } elseif ($value == '18K' && $option_value == '18K') {
                        $gold_weight = $weight;  // Set weight for 18k gold
                        $metal_purity = '18k';  // Mark gold purity as 18k
                    }

                    if (is_numeric($value)) {
                        $selected_size = (int) $value;
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
                    }
                }
            }
        }

        // Calculate price based on selected options
        // $diamond_price = 0;
        if ($diamond_type == 'natural') {
            $diamond_price = $diamond_price;
            $diamond_name = 'Natural Diamond';
        }

        if ($diamond_type == 'labgrown') {
            $diamond_name = 'Lab Grown Diamond';
            $diamond_price = $diamond_weight * $labgrown_diamond_price_per_carat;
        }
        $gold_price = 0;
        if ($metal_purity == '14k') {
            // $gold_price = $gold_weight * $gold_price_14k_per_gram;
            $gold_price = $gold_weight * config('plugins.ecommerce.general.gold_price.14K'); // Example price for 14k gold (per gram)
        } elseif ($metal_purity == '18k') {
            // $gold_price = $gold_weight * $gold_price_18k_per_gram;
            $gold_price = $gold_weight * config('plugins.ecommerce.general.gold_price.18K'); // Example price for 18k gold (per gram)
        }


        $certificate_charges = (float) config('plugins.ecommerce.general.certificate_charge.India');

        $making_charges = (float) config('plugins.ecommerce.general.making_charge.India');

        if ($gold_weight <= 5) {

            $making_charges *= 5;
        } else {

            $making_charges *= $gold_weight;
        }

        $price = round($gold_price, 2);

        $final_price = $price + $making_charges + $certificate_charges + $diamond_price;

        $tax = $final_price * $tax_info->percentage / 100;

        $total_price_with_tax = $tax + $final_price;

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

                // 'option_weight' => $option_weight,

                'original_price' => $price,

                // 'option_price' => $option_price,

                'tax' => $tax,

                'price' => round($price),

                'total' => round($total_price_with_tax) * $qty,
            ];
        return $arr;
    }
}
