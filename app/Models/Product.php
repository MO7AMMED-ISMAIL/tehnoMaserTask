<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'image',
        'price',
    ];

    public function attributes()
    {
        return $this->hasMany(Attribute::class);
    }

    public function categories(){
        return $this->belongsToMany(Category::class);
    }

    

}
