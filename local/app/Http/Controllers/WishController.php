<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Validator;
use App\Wish;
use App\Wishlist;
use App\FavoriteBookmark;

class WishController extends Controller
{
    protected $wish;
    protected $wishlist;
    protected $favoritebookmark;
    protected $hostUrl = 'images.wishare.net';

    public function __construct()
    {
        $this->wish = new Wish();
        $this->wishlist = new Wishlist();
        $this->favoritebookmark = new FavoriteBookmark();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($wishlistid)
    {
        $wish['wishlistid'] = $wishlistid;

        $validation = Validator::make($wish,[
                            'wishlistid'=>'exists:wishes,wishlistid',
                        ]);

        if($validation->fails())
        {
            $failedRules = $validation->failed();
            $field = '';

            if(isset($failedRules['wishlistid']['Exists']))
            {
                $message = 'Wishlist id not found.';
                $field = 'wishlistid';
            }

            return response()->json(['status'=>'400','message'=>$message,'field'=>$field]);
        }
        else
        {
            $wishes = $this->wish->getWishesByWishlistId($wishlistid);
            $userid = $this->wishlist->getCreatedById($wishlistid);

            $getFavoriteBookmarkForStream = $this->favoritebookmark->getFavoriteBookmarkForStream($userid);
            foreach($wishes as $w)
            {
                $w->favorited = '0';
                $w->bookmarked = '0';
                foreach($getFavoriteBookmarkForStream as $gfbfs)
                {
                    if($w->id == $gfbfs['wishid'])
                    {
                        if($gfbfs['type'] == '2')
                        {
                            $w->favorited  = '1';
                        }
                        if($gfbfs['type'] == '1')
                        {
                            $w->bookmarked = '1';
                        }
                    }
                }
                $w->favoritecount = $this->favoritebookmark->countFavoriteBookmark($w->id, '2');
                $w->bookmarkcount = $this->favoritebookmark->countFavoriteBookmark($w->id, '1');
            }

            return response()->json(['status'=>'200','message'=>'Ok','wishes'=>$wishes]);
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
    public function store(Request $request, $userid,$wishlistid)
    {
        $wishDetails['createdby_id'] = $userid;
        $wishDetails['wishlistid'] = $wishlistid;

        if($request['title'] != '')
            $wishDetails['title'] = $request['title'];
        if($request['details'] != '')
            $wishDetails['details'] = $request['details'];
        if($request['alternatives'] != '')
            $wishDetails['alternatives'] = $request['alternatives'];
        if($request['due_date'] != '')
            $wishDetails['due_date'] = $request['due_date'];
        if($request['flagged'] != '')
            $wishDetails['flagged'] = $request['flagged'];
        else
            $wishDetails['flagged'] = 0;

        $validation = Validator::make($wishDetails,[
                        'wishlistid'=>'exists:wishlists,id',
                        'title'=>'required|min:2|max:30',
                        'details'=>'max:100',
                        'alternatives'=>'max:50',
                        //'due_date'=>'after:yesterday',
                        'flagged'=>'in:0,1',
                    ]);

        if($validation->fails())
        {
            $failedRules = $validation->failed();
            $field = '';

            if(isset($failedRules['wishlistid']['Exists']))
            {
                $message = 'Wishlist id not found.';
                $field = 'wishlistid';
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
                $message = 'Title must not exceed 30.';
                $field = 'title';
            }
            else if(isset($failedRules['details']['Max']))
            {
                $message = 'Specifics must not exceed 100 characters';
                $field = 'details';
            }
            else if(isset($failedRules['alternatives']['Max']))
            {
                $message = 'Alternatives must not exceed 50 characters.';
                $field = 'alternatives';
            }
            /*else if(isset($failedRules['due_date']['After']))
            {
                $message = 'Due date must exceed current date.';
                $field = 'due_date';
            }
            else if(isset($failedRules['due_date']['DateFormat']))
            {
                $message = 'Due date must exceed current date.';
                $field = 'due_date';
            }*/
            else if(isset($failedRules['flagged']['In']))
            {
                $message = 'Flagged must be only 1 or 0.';
                $field = 'flagged';
            }

            return response()->json(['status'=>'400','message'=>$message,'field'=>$field]);
        }
        else
        {
            $wish = $this->wish->createWish($wishDetails);

            if($request['image'] != '')
            {
                $wishId = $wish['id'];
                //IMAGE PATH FOR SERVER
                $wishImagePath = "/var/www/images.wishare.net/public_html/wishareimages/wishimages/".$wishId.".jpeg";
                //IMAGE PATH FOR LOCAL
                // $wishImagePath = "C:xampp/htdocs/wishareimages/wishimages/".$wishId.".jpeg";
                //put image to directory
                file_put_contents($wishImagePath, base64_decode($request['image']));
                $wishImageUrl = "http://".$this->hostUrl."/wishimages/".$wishId.".jpeg";
                $wish = $this->wish->addImageUrl($wishId, $wishImageUrl);
            }
            
            return response()->json(['status'=>'201','message'=>'Created wish.', 'wish'=>$wish]);
        }
    }

    public function test()
    {

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($wishid, Request $request)
    {
        $wishDetails = array(
                'wishid'=>$wishid,
                'userid'=>$request['userid'],
            );

        $validation = Validator::make($wishDetails,[
                        'wishid'=>'numeric|exists:wishes,id',
                        'userid'=>'numeric|exists:wishare_users,id'
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

            return response()->json(['status'=>'400','message'=>$message,'field'=>$field]);
        }
        else
        {
            $userid = $request['userid'];

            $getSingleWish = $this->wish->getSingleWish($wishid);

            if($getSingleWish != null)
            {
                $getFavoriteBookmarkForStream = $this->favoritebookmark->getFavoriteBookmarkForStream($userid);
                $getSingleWish->favorited = '0';
                $getSingleWish->bookmarked = '0';

                foreach($getFavoriteBookmarkForStream as $gfbfs)
                {
                    if($getSingleWish->wishid == $gfbfs['wishid'])
                    {
                        if($gfbfs['type'] == '2')
                        {
                            $getSingleWish->favorited  = '1';
                        }
                        if($gfbfs['type'] == '1')
                        {
                            $getSingleWish->bookmarked = '1';
                        }
                    }
                }
                $getSingleWish->favoritecount = $this->favoritebookmark->countFavoriteBookmark($getSingleWish->wishid, '2');
                $getSingleWish->bookmarkcount = $this->favoritebookmark->countFavoriteBookmark($getSingleWish->wishid, '1');
                
                return response()->json(['status'=>'200','message'=>'Ok','wish'=>$getSingleWish]);
            }
            else
            {
                return response()->json(['status'=>'400','message'=>'Wish id not found.']);
            }
        }
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
        /*$wishDetails = array(
                        'wishlistid'=>$request['wishlistid'],
                        'title'=>$request['title'],
                        'details'=>$request['details'],
                        'alternatives'=>$request['alternatives'],
                        'flagged'=>$request['flagged'],
                        'due_date'=>$request['due_date']
                    ); */

        if($request['wishlistid'] != '')
            $wishDetails['wishlistid'] = $request['wishlistid'];
        if($request['title'] != '')
            $wishDetails['title'] = $request['title'];
        if(isset($request['details']))
            $wishDetails['details'] = $request['details'];
        if(isset($request['alternatives']))
            $wishDetails['alternatives'] = $request['alternatives'];
        if($request['flagged'] != '')
            $wishDetails['flagged'] = $request['flagged'];
        if($request['due_date'] != '')
            $wishDetails['due_date'] = $request['due_date'];

        $validation = Validator::make($wishDetails,[
                        'wishlistid'=>'exists:wishlists,id',
                        'title'=>'min:2|max:30',
                        'details'=>'max:100',
                        'alternatives'=>'max:50',
                        //'due_date'=>'after:yesterday',
                        'flagged'=>'in:0,1',
                    ]);

        if($validation->fails())
        {
            $failedRules = $validation->failed();
            $field = '';

            if(isset($failedRules['wishlistid']['Exists']))
            {
                $message = 'Wishlist id not found.';
                $field = 'wishlistid';
            }
            else if(isset($failedRules['title']['Min']))
            {
                $message = 'Title must be atleast 2 characters.';
                $field = 'title';
            }
            else if(isset($failedRules['title']['Max']))
            {
                $message = 'Title must not exceed 30 characters.';
                $field = 'title';
            }
            else if(isset($failedRules['details']['Max']))
            {
                $message = 'Specifics must not exceed 100 characters';
                $field = 'details';
            }
            else if(isset($failedRules['alternatives']['Max']))
            {
                $message = 'Alternatives must not exceed 30 characters.';
                $field = 'alternatives';
            }
            /*else if(isset($failedRules['due_date']['After']))
            {
                $message = 'Due date must exceed current date.';
                $field = 'due_date';
            }
            else if(isset($failedRules['due_date']['DateFormat']))
            {
                $message = 'Due date must exceed current date.';
                $field = 'due_date';
            }*/
            else if(isset($failedRules['flagged']['In']))
            {
                $message = 'Flagged must be only 1 or 0.';
                $field = 'flagged';
            }

            return response()->json(['status'=>'400','message'=>$message,'field'=>$field]);
        }
        else
        {
            $wish = $this->wish->updateWish($wishid, $wishDetails);
            if($wish == null)
            {
                return response()->json(['status'=>'400', 'message'=>'Wish id doesn\'t exist.']);
            }
            else
            {
                if($request['image'] != '')
                {
                    $wishId = $wish['id'];
                    //IMAGE PATH FOR SERVER
                    $wishImagePath = "/var/www/images.wishare.net/public_html/wishareimages/wishimages/".$wishId.time().".jpeg";
                    //IMAGE PATH FOR LOCAL
                    // $wishImagePath = "C:xampp/htdocs/wishareimages/wishimages/".$wishId.time().".jpeg";
                    //put image to directory
                    file_put_contents($wishImagePath, base64_decode($request['image']));
                    $wishImageUrl = "http://".$this->hostUrl."/wishimages/".$wishId.time().".jpeg";
                    $wish = $this->wish->addImageUrl($wishId, $wishImageUrl);
                }
                
                return response()->json(['status'=>'200','message'=>'Updated wish.', 'wish'=>$wish]);
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
        $wishDetails['id'] = $id;

        $validation = Validator::make($wishDetails,[
                'id'=>'exists:wishes,id'
            ]);

        if($validation->fails())
        {
            $failedRules = $validation->failed();
            $field = '';

            if(isset($failedRules['id']['Exists']))
            {
                $message = 'Wish id not found.';
                $field = 'wishid';
            }

            return response()->json(['status'=>'400','message'=>$message,'field'=>$field]);
        }
        else
        {
            $deleteWish = $this->wish->deleteWish($id);

            if($deleteWish)
                return response()->json(['status'=>'200','message'=>'Wish deleted.']);
            else
                return response()->json(['status'=>'500','message'=>'Internal server error.']);
        }
    }
}
