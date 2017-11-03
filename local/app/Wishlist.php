<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Wishlist extends Model
{
    protected $fillable = [
    					'createdby_id',
    					'title',
    					'privacy',
    					'status'
    				];

    public function createWishlist($wishlistDetails)
    {
    	$wishlistDetails['status'] = 1;

    	$createWishlist = Wishlist::create($wishlistDetails);

    	if($createWishlist)
    		return Wishlist::find($createWishlist->id);
    }

    public function getWishlistsByUserId($userid)
    {
    	$wishlists = Wishlist::where('createdby_id', $userid)
                     ->where('status','1')
                     ->orderBy('title', 'asc')
                     ->get();
    	return $wishlists;
    }

    public function updateWishlist($id, $wishlistDetails)
    {
        $updateWishlist = Wishlist::where('id','=',$id)->update($wishlistDetails);

        if($updateWishlist)
            return Wishlist::find($id);
    }

    public function deleteWishlist($id)
    {
        $wishlistDetails['status'] = 0;

        $updateWishlist = Wishlist::where('id','=',$id)->update($wishlistDetails);

        return $updateWishlist;
    }

    public function countUserWishlist($createdby_id)
    {
        return Wishlist::where('createdby_id','=',$createdby_id)
               ->where('status','=','1')
               ->count();
    }

    public function getCreatedById($wishlistid)
    {
        return Wishlist::where('id','=',$wishlistid)
               ->pluck('createdby_id');
    }
}
