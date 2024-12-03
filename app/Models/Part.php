<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Part extends Model
{
    use HasFactory;

    protected $fillable = ['episode_id', 'position', 'description', 'title'];

    public function episode()
    {
        return $this->belongsTo(Episode::class);
    }
}
