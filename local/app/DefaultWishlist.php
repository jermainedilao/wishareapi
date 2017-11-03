<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DefaultWishlist extends Model
{
    protected $table = 'default_wishlists';

    protected $fillable = ['title', 'status'];
}
