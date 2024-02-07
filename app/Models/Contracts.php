<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contracts extends Model
{
    use HasFactory;
    protected $connection = 'incorporacion';
    protected $table = 'contracts';
    protected $guarded = []; 
    public $timestamps = false;
    protected $primaryKey = "code";
    
    public function controlci(){
        // return $this->hasOne(Control_CI::class, 'codigo', 'code');
        // $test = Control_CI::where('codigo',$this->code)->get();

        // return $test;

        return $this->hasOne('App\Models\Control_CI','codigo','code');
    }
}
