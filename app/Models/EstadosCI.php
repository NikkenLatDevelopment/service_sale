<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstadosCI extends Model
{
    use HasFactory;
    protected $connection = 'REG_STG';
    protected $table = 'temporarys.ESTADOSCI';
    protected $guarded = []; 
    public $timestamps = false;
}
