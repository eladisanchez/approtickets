<?php

namespace ApproTickets\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Category extends Model {

    use SoftDeletes;
    use HasFactory;

	protected $table = 'categories';
	protected $guarded = ['id'];
	protected $hidden = ['created_at', 'updated_at'];


    protected static function boot() 
    {
        parent::boot();
        static::addGlobalScope('order', function (Builder $builder) {
            $builder->orderBy('order', 'asc');
        });
    }

    public function products($target=NULL)
    {
        return $this->hasMany(Product::class)->orderBy('order');
    }

}