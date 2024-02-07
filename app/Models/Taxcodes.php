<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Taxcodes extends Model
{
    use HasFactory;
    protected $connection = 'REG_STG';
    protected $table = 'Taxcodes';
    protected $guarded = []; 
    protected $primaryKey = "id_conf";
}
