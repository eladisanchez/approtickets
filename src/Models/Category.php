<?php

namespace ApproTickets\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Translatable\HasTranslations;

class Category extends Model
{

    use SoftDeletes;
    use HasFactory;
    use HasTranslations;

    protected $table = 'categories';
    protected $guarded = ['id'];
    protected $hidden = ['created_at', 'updated_at'];

    public $translatable = [
        'title',
        'summary'
    ];

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope('order', function (Builder $builder) {
            $builder->orderBy('order', 'asc');
        });
    }

    public function products($target = NULL)
    {
        return $this->hasMany(Product::class)->active()->orderBy('order');
    }

}