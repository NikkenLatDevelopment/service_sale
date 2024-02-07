<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warehouses extends Model
{
    use HasFactory;
    protected $connection = 'REG_STG';
    protected $table = 'NIKKENREG_STG.dbo.Whscodes';
    protected $guarded = []; 
}
