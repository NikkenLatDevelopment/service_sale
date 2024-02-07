<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class sales_products extends Model
{
    use HasFactory;
    protected $connection = 'mysqlTV';
    protected $table = 'sale_products';
    protected $guarded = [];     
}
