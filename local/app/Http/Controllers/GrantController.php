<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Validator;
use App\Wish;
use App\FavoriteBookmark;

class GrantController extends Controller
{
    protected $wish;
    protected $hostUrl = 'images.wishare.net';
    protected $favoritebookmark;

    public function __construct()
    {
        $this->wish = new Wish();
        $this->favoritebookmark = new FavoriteBookmark();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($userid)
    {
        $wishDetails['userid'] = $userid;

        $validation = Validator::make($wishDetails,[
                'userid'=>'exists:wishare_users,id',
            ]);

        if($validation->fails())
        {
            $failedRules = $validation->failed();
            $field = '';

            if(isset($failedRules['userid']['Exists']))
            {
                $message = 'User id not found.';
                $field = 'userid';
            }

            return response()->json(['status'=>'400','message'=>$message,'field'=>$field]);
        }
        else
        {
            $getGrantedWishes = $this->wish->getGrantedWishes($userid);

            $getFavoriteBookmarkForStream = $this->favoritebookmark->getFavoriteBookmarkForStream($userid);
            foreach($getGrantedWishes as $ggw)
            {
                $ggw->favorited = '0';
                $ggw->bookmarked = '0';
                foreach($getFavoriteBookmarkForStream as $gfbfs)
                {
                    if($ggw->id == $gfbfs['wishid'])
                    {
                        if($gfbfs['type'] == '2')
                        {
                            $ggw->favorited  = '1';
                        }
                        if($gfbfs['type'] == '1')
                        {
                            $ggw->bookmarked = '1';
                        }
                    }
                }
                $ggw->favoritecount = $this->favoritebookmark->countFavoriteBookmark($ggw->id, '2');
                $ggw->bookmarkcount = $this->favoritebookmark->countFavoriteBookmark($ggw->id, '1');
            }

            return response()->json(['status'=>'200','message'=>'Ok','wishes'=>$getGrantedWishes]);
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
        $grantRequestDetails = [
                'wishid'=>$wishid,
                'granterid'=>$request['granterid'],
                'granteddetails'=>$request['granteddetails'],
                'grantedimageurl'=>$request['grantedimageurl'],
            ];

        $validation = Validator::make($grantRequestDetails,[
                'wishid'=>'exists:wishes,id|numeric',
                'granterid'=>'required|numeric',
                'granteddetails'=>'max:100',
            ]); 

        if($validation->fails())
        {
            $failedRules = $validation->failed();
            $field = '';

            if(isset($failedRules['wishid']['Numeric']))
            {
                $message = 'Invalid wishid format.';
                $field = 'wishid';
            }
            else if(isset($failedRules['wishid']['Exists']))
            {
                $message = 'Wish id not found.';
                $field = 'wishid';
            }
            else if(isset($failedRules['granterid']['Required']))
            {
                $message = 'Granter id is required.';
                $field = 'granterid';
            }
            else if(isset($failedRules['granterid']['Numeric']))
            {
                $message = 'Invalid granter id format.';
                $field = 'granterid';
            }
            else if(isset($failedRules['granteddetails']['Max']))
            {
                $message = 'Details must not exceed 100 characters.';
                $field = 'granteddetails';
            }

            return response()->json(['status'=>'400','message'=>$message,'field'=>$field]);
        }
        else
        {
            $grantRequestDetails = [
                'granterid'=>$request['granterid'],
                'granteddetails'=>$request['granteddetails'],
                'grantedimageurl'=>$request['grantedimageurl'],
            ];

            $grantRequest = $this->wish->grantRequest($wishid, $grantRequestDetails);

            if($request['grantedimageurl'] != '' && $grantRequest != null)
            {
                $wishId = $grantRequest['id'];
                //IMAGE PATH FOR SERVER
                $grantedWishImagePath = "/var/www/images.wishare.net/public_html/wishareimages/wishimages/".$wishId.time().".jpeg";
                //IMAGE PATH FOR LOCAL
                // $grantedWishImagePath = "C:/xampp/htdocs/wishareimages/wishimages/".$wishId.time().".jpeg";
                //put image to directory
                file_put_contents($grantedWishImagePath, base64_decode($request['grantedimageurl']));
                $grantedWishImageUrl = "http://".$this->hostUrl."/wishimages/".$wishId.time().".jpeg";
                $grantRequest = $this->wish->addGrantedImageUrl($wishId, $grantedWishImageUrl);
            }

            if($grantRequest != null)
                return response()->json(['status'=>'200','message'=>'Sent grant request.','wish'=>$grantRequest]);
            else
                return response()->json(['status'=>'409','message'=>'Wish has already been granted by other user.']);
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
    public function update(Request $request, $wishid)
    {
        $wishDetails['wishid'] = $wishid;
        $wishDetails['action'] = $request['action'];

        $validation = Validator::make($wishDetails, [
                'wishid'=>'exists:wishes,id',
                'action'=>'required|in:accept,decline'
            ]);

        if($validation->fails())
        {
            $failedRules = $validation->failed();
            $field = '';

            if(isset($failedRules['wishid']['Exists']))
            {
                $message = 'Wish id not found.';
                $field = 'wishid';
            }
            else if(isset($failedRules['action']['Required']))
            {
                $message = 'Action is required.';
                $field = 'action';
            }
            else if(isset($failedRules['action']['In']))
            {
                $message = 'Action must be either accept or decline only.';
                $field = 'action';
            }

            return response()->json(['status'=>'400','message'=>$message,'field'=>$field]);
        }
        else
        {
            if($wishDetails['action'] == 'accept')
            {
                $wish = $this->wish->acceptWishGrant($wishid);
                $message = 'Accepted grant request.';
            }
            else if($wishDetails['action'] = 'decline')
            {
                $wish = $this->wish->declineWishGrant($wishid);
                $message = 'Declined grant request.';
            }

            return response()->json(['status'=>'200','message'=>$message,'wish'=>$wish]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($wishid)
    {
        $wishDetails['wishid'] = $wishid;

        $validation = Validator::make($wishDetails, [
                'wishid'=>'exists:wishes,id|numeric',
            ]);

        if($validation->fails())
        {
            $failedRules = $validation->failed();
            $field = '';

            if(isset($failedRules['wishid']['Numeric']))
            {
                $message = 'Invalid wish id format.';
                $field = 'wishid';
            }
            else if(isset($failedRules['wishid']['Exists']))
            {
                $message = 'Wish id not found.';
                $field = 'wishid';
            }

            return response()->json(['status'=>'400','message'=>$message,'field'=>$field]);
        }
        else
        {
            $ungrantWish = $this->wish->ungrantWish($wishid);
            return response()->json(['status'=>'200','message'=>'Ungranted wish.','wish'=>$ungrantWish]);
        }
    }

    public function getPendingGrantRequest($userid)
    {
        $wishDetails['userid'] = $userid;

        $validation = Validator::make($wishDetails, [
                'userid'=>'exists:wishare_users,id',
            ]);

        if($validation->fails())
        {
            $failedRules = $validation->failed();
            $field = '';

            if(isset($failedRules['userid']['Exists']))
            {
                $message = 'User id not found.';
                $field = 'userid';
            }

            return response()->json(['status'=>'400','message'=>$message,'field'=>$field]);
        }
        else
        {
            $getPendingGrantRequest = $this->wish->getPendingGrantRequest($wishDetails);

            return response()->json(['status'=>'200','message'=>'Ok','pendinggrantrequests'=>$getPendingGrantRequest]);
        }    
    }

    public function getGivenWishes($userid)
    {
        $wishDetails['userid'] = $userid;

        $validation = Validator::make($wishDetails,[
                'userid'=>'exists:wishare_users,id',
            ]);

        if($validation->fails())
        {
            $failedRules = $validation->failed();
            $field = '';

            if(isset($failedRules['userid']['Exists']))
            {
                $message = 'User id not found.';
                $field = 'userid';
            }

            return response()->json(['status'=>'400','message'=>$message,'field'=>$field]);
        }
        else
        {
            $getGivenWishes = $this->wish->getGivenWishes($userid);

            $getFavoriteBookmarkForStream = $this->favoritebookmark->getFavoriteBookmarkForStream($userid);

            foreach($getGivenWishes as $ggw)
            {
                $ggw->favorited = '0';
                $ggw->bookmarked = '0';
                foreach($getFavoriteBookmarkForStream as $gfbfs)
                {
                    if($ggw->id == $gfbfs['wishid'])
                    {
                        if($gfbfs['type'] == '2')
                        {
                            $ggw->favorited  = '1';
                        }
                        if($gfbfs['type'] == '1')
                        {
                            $ggw->bookmarked = '1';
                        }
                    }
                }
                $ggw->favoritecount = $this->favoritebookmark->countFavoriteBookmark($ggw->id, '2');
                $ggw->bookmarkcount = $this->favoritebookmark->countFavoriteBookmark($ggw->id, '1');
            }
            
            return response()->json(['status'=>'200','message'=>'Ok','wishes'=>$getGivenWishes]);
        }
    }
}
