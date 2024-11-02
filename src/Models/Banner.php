<?php

namespace ApproTickets\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Translatable\HasTranslations;

class Banner extends Model
{

    use SoftDeletes;
    use HasTranslations;

    protected $table = 'banners';
    protected $guarded = ['id'];
    protected $with = ['product'];

    public $translatable = [
        'title',
    ];
    protected $useFallbackLocale = "ca";

    protected static function booted()
    {
        static::creating(function ($banner) {
            $banner->order = 0;
        });
    }

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope('order', function (Builder $builder) {
            $builder->orderBy('order', 'asc');
        });
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function scopeActive($query)
    {
        return $query->where('date_start', '<=', now())
            ->where('date_end', '>=', now());
    }

}
