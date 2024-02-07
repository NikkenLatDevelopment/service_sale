<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contracts_test extends Model
{
    use HasFactory;
    protected $connection = 'incorporacion';
    protected $table = 'contracts_test';
    protected $guarded = []; 
    public $timestamps = false;
    protected $primaryKey = "id_contract";
}
