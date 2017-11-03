<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Validator;
use App\Wishlist;

class WishlistController extends Controller
{
    protected $wishlist;

    public function __construct()
    {
        $this->wishlist = new Wishlist();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($userid)
    {
        $wishlist['createdby_id'] = $userid;

        $validation = Validator::make($wishlist,[
                        'createdby_id'=>'exists:wishare_users,id'
                    ]);

        if($validation->fails())
        {
            $failedRules = $validation->failed();
            $field = '';

            if(isset($failedRules['createdby_id']['Exists']))
            {
                $message = 'User id doesn\'t exist.';
                $field = 'userid';
            }

            return response()->json(['status'=>'400','message'=>$message,'field'=>$field]);
        }
        else
        {
            $wishlists = $this->wishlist->getWishlistsByUserId($userid);
            return response()->json(['status'=>'200', 'message'=>'Ok','wishlists'=>$wishlists]);
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
    public function store(Request $request, $userid)
    {
        $wishlistDetails = array(
                'createdby_id'=>$userid,
                'title'=>$request['title']
            );

        if(!isset($request['privacy']))
            $wishlistDetails['privacy'] = 0;
        else
            $wishlistDetails['privacy'] = $request['privacy'];

        $validation = Validator::make($wishlistDetails,[
                'createdby_id'=>'required|numeric|exists:wishare_users,id',
                'title'=>'required|min:2|max:20',
                'privacy'=>'in:0,1'
            ]);

        if($validation->fails())
        {
            $failedRules = $validation->failed();
            $field = '';

            if(isset($failedRules['createdby_id']['Required']))
            {
                $message = 'Userid is required.';
                $field = 'userid';
            }
            else if(isset($failedRules['createdby_id']['Numeric']))
            {
                $message = 'Invalid userid format.';
                $field = 'userid';
            }
            else if(isset($failedRules['createdby_id']['Exists']))
            {
                $message = 'Userid not found.';
                $field = 'userid';
            }
            else if(isset($failedRules['title']['Required']))
            {
                $message = 'Title is required.';
                $field = 'title';
            }
            else if(isset($failedRules['title']['Min']))
            {
                $message = 'Title must be atleast 2 characters.';
                $field = 'title';
            }
            else if(isset($failedRules['title']['Max']))
            {
                $message = 'Title must not exceed 20 characters.';
                $field = 'title';
            }
            else if(isset($failedRules['privacy']['In']))
            {
                $message = 'Privacy must be only 1 or 0 only.';
                $field = 'privacy';
            }

            return response()->json(['status'=>'400', 'message'=>$message, 'field'=>$field]);
        }
        else
        {
            $wishlist = $this->wishlist->createWishlist($wishlistDetails);
            return response()->json(['status'=>'201', 'message'=>'Wishlist created.','wishlist'=>$wishlist]);
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
    public function update(Request $request, $wishlistid)
    {
        $wishlistDetails = array(
                'title'=>$request['title'],
            );

        if($request['privacy'] != '')
            $wishlistDetails['privacy'] = $request['privacy'];

        $validation = Validator::make($wishlistDetails,[
                'title'=>'required|min:2|max:20',
                'privacy'=>'in:0,1'
            ]);

        if($validation->fails())
        {
            $failedRules = $validation->failed();
            $field = '';

            if(isset($failedRules['title']['Required']))
            {
                $message = 'Title is required.';
                $field = 'title';
            }
            else if(isset($failedRules['title']['Min']))
            {
                $message = 'Title must be atleast 2 characters.';
                $field = 'title';
            }
            else if(isset($failedRules['title']['Max']))
            {
                $message = 'Title must not exceed 20 characters.';
                $field = 'title';
            }
            else if(isset($failedRules['privacy']['In']))
            {
                $message = 'Privacy must be only 1 or 0.';
                $field = 'privacy';
            }

            return response()->json(['status'=>'400', 'message'=>$message, 'field'=>$field]);
        }
        else
        {
            $wishlist = $this->wishlist->updateWishlist($wishlistid, $wishlistDetails);
            
            if($wishlist == null)
            {
                return response()->json(['status'=>'400', 'message'=>'Wishlist id doesn\'nt exist.']);
            }
            else
            {   
                return response()->json(['status'=>'200', 'message'=>'Wishlist updated.','wishlist'=>$wishlist]);
            }
        }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $wishlistDetails['id'] = $id;

        $validation = Validator::make($wishlistDetails,[
                'id'=>'exists:wishlists,id'
            ]);

        if($validation->fails())
        {
            $failedRules = $validation->failed();
            $field = '';

            if(isset($failedRules['id']['Exists']))
            {
                $message = 'Wishlist id not found.';
                $field = 'wishlistid';
            }

            return response()->json(['status'=>'400','message'=>$message,'field'=>$field]);
        }
        else
        {
            $deleteWish = $this->wishlist->deleteWishlist($id);

            if($deleteWish)
                return response()->json(['status'=>'200','message'=>'Wishlist deleted.']);
            else
                return response()->json(['status'=>'500','message'=>'Internal server error.']);
        }
    }

    public function getWishlistsByUserId($userid)
    {
        
    }
}
