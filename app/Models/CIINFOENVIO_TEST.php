<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CIINFOENVIO_TEST extends Model
{
    use HasFactory;
    protected $connection = '170';
    protected $table = 'NIKKENREG_STG_TEST.dbo.CIINFOENVIO';
    protected $guarded = []; 
    public $timestamps = false;
}
