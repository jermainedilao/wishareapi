<?php

namespace App;

use Illuminate\Contracts\Support\JsonableInterface;
use Illuminate\Database\Eloquent\Model;
use DB;

class Wish extends Model
{
    protected $fillable = array(
                        'createdby_id',
    					'wishlistid',
                        'title',
                        'details',
                        'alternatives',
                        'due_date',
                        'flagged',
                        'status',
                        'wishimageurl',  
    				);

    public function createWish($wishDetails)
    {
    	$wishDetails['status'] = 1;
    	$createWish = Wish::create($wishDetails);

    	if($createWish)
    		return Wish::find($createWish->id);
        //return $createWish->id;
    }

    public function addImageUrl($id, $wishimageurl)
    {
        $wish = Wish::find($id);
        $wish->wishimageurl = $wishimageurl;
        $addImageUrl = $wish->save();

        if($addImageUrl)
            return Wish::find($id);
    }

    public function getWishesByWishlistId($wishlistid)
    {
        $wishes = Wish::where('wishlistid','=',$wishlistid)
                  ->where('status','=',1)
                  ->orderBy('flagged','desc')
                  ->orderBy('created_at','desc')
                  ->get();
        return $wishes;
    }

    public function updateWish($id, $wishDetails)
    {
        $updateWish = Wish::where('id','=',$id)->update($wishDetails);

        if($updateWish)
            return Wish::find($id);
    }

    public function deleteWish($id)
    {
        $wishDetails['status'] = 0;
        $deleteWish = Wish::where('id','=',$id)->update($wishDetails);

        return $deleteWish;
    }

    public function countUserWish($createdby_id)
    {
        return Wish::where('createdby_id','=',$createdby_id)
               ->where('status','=','1')
               ->count();
    }

    public function countUserGranted($createdby_id)
    {
        return Wish::where('createdby_id','=',$createdby_id)
               ->where('granted','=','1')
               ->where('status','=','1')
               ->count();
    }

    public function countUserGiven($userid)
    {
        return Wish::where('granterid','=',$userid)
               ->where('granted','=','1')
               ->where('status','=','1')
               ->count();
    }

    public function getStream($friendsId, $limit)
    {
        $getStream = DB::table('wishes')
                     ->leftJoin('wishare_users AS wisher','wisher.id','=','wishes.createdby_id')
                     ->leftJoin('wishare_users AS granter','granter.id','=','wishes.granterid')
                     ->leftJoin('wishlists','wishlists.id','=','wishes.wishlistid')
                     ->select('wisher.id AS userid','wisher.username','wisher.imageurl',
                              'wishes.id AS wishid','wishes.wishlistid','wishes.title','wishes.details',
                              'wishes.wishimageurl','wishes.alternatives','wishes.due_date','wishes.granted',
                              'wishes.granterid','wishes.granteddetails','wishes.grantedimageurl',DB::raw('IFNULL(granter.username, "") AS granterusername'),
                              DB::raw('IFNULL(granter.imageurl, "") AS granterimageurl'))
                     ->where('wishes.status','=','1')
                     ->where('wishlists.privacy','=','0')
                     ->whereIn('wishes.createdby_id',$friendsId)
                     ->orderBy('wishes.updated_at','desc')
                     ->paginate($limit);
                     //->get();

        return $getStream;
    }

   public function grantRequest($wishid, $grantRequestDetails)
   {
      $checkIfCreatedByIdIsEqualsGranterId = Wish::where('id','=',$wishid)
                                        ->where('createdby_id','=',$grantRequestDetails['granterid'])
                                        ->count();

      if($checkIfCreatedByIdIsEqualsGranterId > 0)
      {                                        
        $grantRequestDetails['granted'] = '1';
        $grantRequest = Wish::where('id','=',$wishid)
                        ->where('granterid','=','0')
                        ->update($grantRequestDetails);
      }
      else
      {
        $grantRequest = Wish::where('id','=',$wishid)
                        ->where('granterid','=','0')
                        ->update($grantRequestDetails); 
      }
      if($grantRequest)
         return Wish::find($wishid);
   }

   public function addGrantedImageUrl($id, $grantedimageurl)
   {
      $wish = Wish::find($id);
      $wish->grantedimageurl = $grantedimageurl;
      $addGrantedImageUrl = $wish->save();

      if($addGrantedImageUrl)
         return Wish::find($id);
   }

   public function getPendingGrantRequest($wishDetails)
   {
      $createdby_id = $wishDetails['userid'];

      $getPendingGrantRequest = DB::table('wishes')
                                ->leftJoin('wishare_users','wishare_users.id','=','wishes.granterid')
                                ->select('wishes.id', 'wishes.createdby_id', 'wishes.wishlistid','wishes.title',
                                         'wishes.details','wishes.wishimageurl','wishes.alternatives',
                                         'wishes.due_date','wishes.granted','wishes.granterid','wishare_users.username AS granterusername',
                                         'wishare_users.imageurl AS granterimageurl', 'wishes.granteddetails', 'wishes.grantedimageurl')
                                ->where('wishes.createdby_id','=',$createdby_id)
                                ->where('wishes.granterid','<>','0')
                                ->where('wishes.granted','=','0')
                                ->where('wishes.status','=','1')
                                ->orderBy('wishes.updated_at','desc')
                                ->get();

      return $getPendingGrantRequest;
   }

   public function acceptWishGrant($wishid)
   {
      $wishDetails['granted'] = '1';
      $acceptGrant = Wish::where('id','=',$wishid)->update($wishDetails);

      if($acceptGrant)
         return Wish::find($wishid);
   }

   public function declineWishGrant($wishid)
   {
      $wishDetails['granted'] = '0';
      $wishDetails['granterid'] = '0';
      $wishDetails['granteddetails'] = '';
      $wishDetails['grantedimageurl'] = '';

      $declineGrant = Wish::where('id','=',$wishid)->update($wishDetails);

      if($declineGrant)
         return Wish::find($wishid);
   }

   public function ungrantWish($wishid)
   {
      $wishDetails['granted'] = '0';
      $wishDetails['granterid'] = '0';
      $wishDetails['granteddetails'] = '';
      $wishDetails['grantedimageurl'] = '';

      $ungrantWish = Wish::where('id','=',$wishid)->update($wishDetails);

      if($ungrantWish)
         return Wish::find($wishid);
   }

    public function getGrantedWishes($createdby_id)
    {
        return DB::table('wishes')
               ->leftJoin('wishare_users AS granter','wishes.granterid','=','granter.id')
               ->leftJoin('wishare_users AS wisher','wishes.createdby_id','=','wisher.id')
               ->select('wishes.id', 'wishes.createdby_id', 'wishes.wishlistid','wishes.title',
                        'wishes.details','wishes.wishimageurl','wishes.alternatives','wishes.flagged',
                        'wishes.due_date','wishes.granted','wisher.username AS wisherusername','wisher.imageurl AS wisherimageurl',
                        'wishes.granterid','granter.username AS granterusername','wishes.granteddetails', 'wishes.grantedimageurl')
               ->where('wishes.granted','=','1')
               ->where('wishes.createdby_id','=',$createdby_id)
               ->where('wishes.status','=','1')
               ->get();
    }

    public function getGivenWishes($granterid)
    {
        return DB::table('wishes')
               ->leftJoin('wishare_users AS granter','wishes.granterid','=','granter.id')
               ->leftJoin('wishare_users AS wisher','wishes.createdby_id','=','wisher.id')
               ->select('wishes.id', 'wishes.createdby_id', 'wishes.wishlistid','wishes.title',
                        'wishes.details','wishes.wishimageurl','wishes.alternatives','wishes.flagged',
                        'wishes.due_date','wishes.granted','wisher.username AS wisherusername','wisher.imageurl AS wisherimageurl',
                        'wishes.granterid','granter.username AS granterusername','wishes.granteddetails', 'wishes.grantedimageurl')
               ->where('wishes.granted','=','1')
               ->where('wishes.granterid','=',$granterid)
               ->where('wishes.status','=','1')
               ->get();
    }

    public function getSingleWish($wishid)
    {
        $getStream = DB::table('wishes')
                     ->leftJoin('wishare_users AS wisher','wisher.id','=','wishes.createdby_id')
                     ->leftJoin('wishare_users AS granter','granter.id','=','wishes.granterid')
                     ->select('wisher.id AS userid','wisher.username','wisher.imageurl',
                              'wishes.id AS wishid','wishes.wishlistid','wishes.title','wishes.details',
                              'wishes.wishimageurl','wishes.alternatives','wishes.due_date','wishes.granted',
                              'wishes.granterid','wishes.granteddetails','wishes.grantedimageurl',DB::raw('IFNULL(granter.username, "") AS granterusername'),
                              DB::raw('IFNULL(granter.imageurl, "") AS granterimageurl'))
                     ->where('wishes.status','=','1')
                     ->where('wishes.id','=',$wishid)
                     ->first();

        return $getStream;
    }

    public function getBookmarkedWishes($wishesid)
    {
        return DB::table('wishes')
               ->leftJoin('wishare_users AS wisher','wishes.createdby_id','=','wisher.id')
               ->leftJoin('wishlists','wishlists.id','=','wishes.wishlistid')
               ->select('wishes.id', 'wishes.createdby_id', 'wishes.wishlistid','wishes.title',
                        'wishes.details','wishes.wishimageurl','wishes.alternatives','wishes.flagged',
                        'wishes.due_date','wishes.granted','wisher.username AS wisherusername','wisher.imageurl AS wisherimageurl')
               ->whereIn('wishes.id', $wishesid)
               ->where('wishes.granterid','=','0')
               ->where('wishlists.privacy','=','0')
               ->where('wishes.status','=','1')
               ->orderBy('wishes.id','desc')
               ->get();
    }
}
