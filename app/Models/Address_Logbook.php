<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address_Logbook extends Model
{
    use HasFactory;
    protected $connection = 'mysqlTV';
    protected $table = 'sale_address_logbook';
    protected $guarded = []; 
}
