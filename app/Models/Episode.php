<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Episode extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'description'];

    public function parts()
    {
        return $this->hasMany(Part::class)->orderBy('position');
    }
}
