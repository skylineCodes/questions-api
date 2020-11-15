<?php

namespace App\Models;

use App\Models\Traits\CanBeScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory, CanBeScoped;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    public function choices()
    {
        return $this->hasMany('App\Models\Choice');
    }
}
