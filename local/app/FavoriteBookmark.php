<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class FavoriteBookmark extends Model
{
    protected $table = 'favorite_bookmark';
    protected $fillable = [
                        'wishid',
                        'userid',
                        'type',
                    ];

    public function favoriteBookmarkWish($favoriteBookmarkDetails)
    {
        if($favoriteBookmarkDetails['type'] == 'bookmark')
            $favoriteBookmarkDetails['type'] = '1';
        else
            $favoriteBookmarkDetails['type'] = '2';

        $favoriteBookmarkWish = FavoriteBookmark::create($favoriteBookmarkDetails);

        if($favoriteBookmarkWish)
            return true;
        else
            return false;
    }

    public function isUserIdWishIdUnique($favoriteBookmarkDetails)
    {
        if($favoriteBookmarkDetails['type'] == 'bookmark')
            $favoriteDetails['type'] = '1';
        else if($favoriteBookmarkDetails['type'] == 'favorite')
            $favoriteDetails['type'] = '2';

            $isUnique = FavoriteBookmark::where('userid','=',$favoriteBookmarkDetails['userid'])
                        ->where('wishid','=',$favoriteBookmarkDetails['wishid'])
                        ->where('type','=',$favoriteBookmarkDetails['type'])
                        ->count();

            if($isUnique > 0)
                return false;
            else
                return true;
    }

    public function getFavoriteBookmarkForStream($userid)
    {
        $getFavoriteBookmarkForStream = FavoriteBookmark::where('userid','=',$userid)
                                        ->get();

        return $getFavoriteBookmarkForStream;
    }

    public function unfavoriteBookmarkWish($favoriteBookmarkDetails)
    {
        if($favoriteBookmarkDetails['type'] == 'bookmark')
            $favoriteBookmarkDetails['type'] = '1';
        else if($favoriteBookmarkDetails['type'] == 'favorite')
            $favoriteBookmarkDetails['type'] = '2';

        $unfavoriteBookmarkWish = FavoriteBookmark::where('userid','=',$favoriteBookmarkDetails['userid'])
                                  ->where('wishid','=',$favoriteBookmarkDetails['wishid'])
                                  ->where('type','=',$favoriteBookmarkDetails['type'])
                                  ->delete();

        if($unfavoriteBookmarkWish)
            return true;
        else
            return false;
    }

    public function getFavoriteBookmark($userid)
    {
        $activities = DB::table('favorite_bookmark')
                      ->leftJoin('wishare_users','wishare_users.id','=','favorite_bookmark.userid')
                      ->leftJoin('wishes','wishes.id','=','favorite_bookmark.wishid')
                      ->select('favorite_bookmark.wishid','favorite_bookmark.userid','wishare_users.username',
                               'wishare_users.imageurl AS userimageurl','favorite_bookmark.type')
                      ->where('wishes.createdby_id','=',$userid)
                      ->where('favorite_bookmark.userid','<>',$userid)
                      ->where('wishes.status','=','1')
                      ->orderBy('favorite_bookmark.id','desc')
                      ->get();

        return $activities;
    }

    public function countFavoriteBookmark($wishid, $type)
    {
        $countFavoriteBookmark = FavoriteBookmark::where('wishid','=',$wishid)
                                 ->where('type','=',$type)
                                 ->count();
                                 
        return $countFavoriteBookmark;
    }

    public function countUserBookmark($id)
    {
        $countUserBookmark = FavoriteBookmark::where('userid','=',$id)
                             ->where('type','=','1')
                             ->count();
        return $countUserBookmark;
    }

    public function getBookmarkedWishesId($userid)
    {
        $getBookmarkedWishesId = DB::table('favorite_bookmark')
                                 ->select('favorite_bookmark.wishid')
                                 ->where('userid','=',$userid)
                                 ->where('type','=','1')
                                 ->orderBy('id','desc')
                                 ->get();
        return $getBookmarkedWishesId;
    }
}