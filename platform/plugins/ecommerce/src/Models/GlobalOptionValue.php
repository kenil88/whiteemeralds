<?php

namespace Botble\Ecommerce\Models;

use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GlobalOptionValue extends BaseModel
{
    protected $table = 'ec_global_option_value';

    protected $fillable = [
        'option_id',
        'option_value',
        'affect_price',
        'affect_price_usd',
        'affect_type',
        'weight',
        'order',
    ];

    protected $casts = [
        'affect_price' => 'float',
        'affect_price_usd' => 'float',
        'weight' => 'float',
    ];

    public function option(): BelongsTo
    {
        return $this->belongsTo(GlobalOption::class, 'option_id');
    }
}
