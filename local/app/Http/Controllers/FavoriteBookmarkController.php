<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Validator;
use App\FavoriteBookmark;
use App\Wish;

class FavoriteBookmarkController extends Controller
{

    protected $favoritebookmark;
    protected $wish;

    public function __construct()
    {
        $this->favoritebookmark = new FavoriteBookmark();
        $this->wish = new Wish();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($userid)
    {
        $favoriteBookmarkDetails = [
            'userid'=>$userid
        ];

        $validation = Validator::make($favoriteBookmarkDetails, [
                'userid'=>'exists:wishare_users,id|numeric'
            ]);

        if($validation->fails())
        {
            $failedRules = $validation->failed();
            $field = '';

            if(isset($failedRules['userid']['Numeric']))
            {
                $message = 'Invalid user id format.';
                $field = 'userid';
            }
            else if(isset($failedRules['userid']['Exists']))
            {
                $message = 'User id not found.';
                $field = 'userid';
            }

            return response()->json(['status'=>'400','message'=>$message,'field'=>$field]);
        }
        else
        {
            $activities = $this->favoritebookmark->getFavoriteBookmark($userid);
            return response()->json(['status'=>'200','message'=>'Ok','activities'=>$activities]);   
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $wishid)
    {
        $favoriteBookmarkDetails = [
            'userid'=>$request['userid'],
            'wishid'=>$wishid,
            'type'=>$request['type']
        ];

        $validation = Validator::make($favoriteBookmarkDetails,[
                'userid'=>'required|exists:wishare_users,id|numeric',
                'wishid'=>'exists:wishes,id|numeric',
                'type'=>'required|in:favorite,bookmark',
            ]);

        if($validation->fails())
        {
            $failedRules = $validation->failed();
            $field = '';

            if(isset($failedRules['userid']['Required']))
            {
                $message = 'User id is required.';
                $field = 'userid';
            }
            else if(isset($failedRules['userid']['Numeric']))
            {
                $message = 'Invalid userid format.';
                $field = 'userid';
            }
            else if(isset($failedRules['userid']['Exists']))
            {
                $message = 'User id not found.';
                $field = 'userid';
            }
            else if(isset($failedRules['wishid']['Numeric']))
            {
                $message = 'Invalid wishid format.';
                $field = 'wishid';
            }
            else if(isset($failedRules['wishid']['Exists']))
            {
                $message = 'Wish id not found.';
                $field = 'wishid';
            }
            else if(isset($failedRules['type']['Required']))
            {
                $message = 'Type is required.';
                $field = 'type';
            }
            else if(isset($failedRules['type']['In']))
            {
                $message = 'Type must be only favorite or bookmark.';
                $field = 'type';
            }

            return response()->json(['status'=>'400','message'=>$message,'field'=>$field]);
        }
        else
        {
            if($this->favoritebookmark->isUserIdWishIdUnique($favoriteBookmarkDetails))
            {
                $favoriteBookmarkWish = $this->favoritebookmark->favoriteBookmarkWish($favoriteBookmarkDetails);

                if($favoriteBookmarkWish)
                {
                    if($favoriteBookmarkDetails['type'] == 'bookmark')
                        $message = 'Tracked wish.';
                    else if($favoriteBookmarkDetails['type'] == 'favorite')
                        $message = 'Favorited wish.';

                    return response()->json(['status'=>'200','message'=>$message]);   
                }
                else
                {
                    $message = 'Server error has occurred. Please try again later.';
                    return response()->json(['status'=>'500','message'=>$message]);
                }
            }
            else
            {
                if($favoriteBookmarkDetails['type'] == 'bookmark')
                    $message = 'Already tracked wish.';
                else if($favoriteBookmarkDetails['type'] == 'favorite')
                    $message = 'Already favorited wish.';

                return response()->json(['status'=>'400','message'=>$message]);
            }
                
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $wishid)
    {
        $favoriteBookmarkDetails = [
            'userid'=>$request['userid'],
            'wishid'=>$wishid,
            'type'=>$request['type'],
        ];

        $validation = Validator::make($favoriteBookmarkDetails,[
                'userid'=>'required|exists:wishare_users,id|numeric',
                'wishid'=>'exists:wishes,id|numeric',
                'type'=>'required|in:favorite,bookmark',
            ]);

        if($validation->fails())
        {
            $failedRules = $validation->failed();
            $field = '';

            if(isset($failedRules['userid']['Required']))
            {
                $message = 'User id is required.';
                $field = 'userid';
            }
            else if(isset($failedRules['userid']['Numeric']))
            {
                $message = 'Invalid userid format.';
                $field = 'userid';
            }
            else if(isset($failedRules['userid']['Exists']))
            {
                $message = 'User id not found.';
                $field = 'userid';
            }
            else if(isset($failedRules['wishid']['Numeric']))
            {
                $message = 'Invalid wishid format.';
                $field = 'wishid';
            }
            else if(isset($failedRules['wishid']['Exists']))
            {
                $message = 'Wish id not found.';
                $field = 'wishid';
            }
            else if(isset($failedRules['type']['Required']))
            {
                $message = 'Type is required.';
                $field = 'type';
            }
            else if(isset($failedRules['type']['In']))
            {
                $message = 'Type must be only favorite or bookmark.';
                $field = 'type';
            }

            return response()->json(['status'=>'400','message'=>$message,'field'=>$field]);
        }
        else
        {
            $unfavoriteBookmarkWish = $this->favoritebookmark->unfavoriteBookmarkWish($favoriteBookmarkDetails);

            if($unfavoriteBookmarkWish)
            {
                if($favoriteBookmarkDetails['type'] == 'bookmark')
                    $message = 'Untracked wish.';
                else if($favoriteBookmarkDetails['type'] == 'favorite')
                    $message = 'Unfavorited wish.';

                return response()->json(['status'=>'200','message'=>$message]);
            }
            else
            {
                return response()->json(['status'=>'400','message'=>'Details are not found.']);   
            }
        }
    }

    public function getBookmarkedWishes($userid, Request $request)
    {
        $getBookmarkedWishesDetails = [
            'userid'=>$userid,
            'loggeduserid'=>$request['loggeduserid'],
        ];

        $validation = Validator::make($getBookmarkedWishesDetails, [
                'userid'=>'numeric|exists:wishare_users,id',
                'loggeduserid'=>'required|numeric|exists:wishare_users,id'
            ]);

        if($validation->fails())
        {
            $failedRules = $validation->failed();
            $field = '';

            if(isset($failedRules['loggeduserid']['Required']))
            {
                $message = 'Logged user id is required.';
                $field = 'loggeduserid';
            }
            else if(isset($failedRules['userid']['Numeric']))
            {
                $message = 'Invalid id format.';
                $field = 'userid';
            }
            else if(isset($failedRules['userid']['Exists']))
            {
                $message = 'User id doesn\'t exist.';
                $field = 'userid';
            }
            else if(isset($failedRules['loggeduserid']['Numeric']))
            {
                $message = 'Invalid id format.';
                $field = 'loggeduserid';
            }
            else if(isset($failedRules['loggeduserid']['Exists']))
            {
                $message = 'Logged user id doesn\'t exist.';
                $field = 'loggeduserid';
            }

            return response()->json(['status'=>'400','message'=>$message,'field'=>$field]);
        }
        else
        {
            $getBookmarkedWishesId = $this->favoritebookmark->getBookmarkedWishesId($userid);
            foreach($getBookmarkedWishesId as $gbwi)
            {
                $wishesid[] = $gbwi->wishid;
            }

            $getBookmarkedWishes = array();

            if(!empty($wishesid))
            {
                $getBookmarkedWishes = $this->wish->getBookmarkedWishes($wishesid);
                $getFavoriteBookmarkForStream = $this->favoritebookmark->getFavoriteBookmarkForStream($getBookmarkedWishesDetails['loggeduserid']);

                foreach($getBookmarkedWishes as $gbw)
                {
                    $gbw->favorited = '0';
                    $gbw->bookmarked = '0';
                    foreach($getFavoriteBookmarkForStream as $gfbfs)
                    {
                        if($gbw->id == $gfbfs['wishid'])
                        {
                            if($gfbfs['type'] == '2')
                            {
                                $gbw->favorited  = '1';
                            }
                            if($gfbfs['type'] == '1')
                            {
                                $gbw->bookmarked = '1';
                            }
                        }
                        $gbw->favoritecount = $this->favoritebookmark->countFavoriteBookmark($gbw->id, '2');
                        $gbw->bookmarkcount = $this->favoritebookmark->countFavoriteBookmark($gbw->id, '1');
                    }
                }
            }

            return response()->json(['status'=>'200','message'=>'Ok','wishes'=>$getBookmarkedWishes]);
        }
    }
}
