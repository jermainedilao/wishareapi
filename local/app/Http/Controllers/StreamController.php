<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Friend;
use App\WishareUser;
use App\FavoriteBookmark;
use App\Wish;
use Validator;

class StreamController extends Controller
{
    protected $friend;
    protected $wishareUser;
    protected $wish;
    protected $favoritebookmark;
    protected $hostUrl = 'api.wishare.net';

    public function __construct()
    {
        $this->friend = new Friend();
        $this->user = new WishareUser();
        $this->wish = new Wish();
        $this->favoritebookmark = new FavoriteBookmark();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $userid)
    {   
        $userDetails['userid'] = $userid;
        $userDetails['offset'] = 1;
        $userDetails['limit'] = 10;

        if($request['offset'] != '')
            $userDetails['offset'] = $request['offset'];
        if($request['limit'] != '')
            $userDetails['limit'] = $request['limit'];

        $validation = Validator::make($userDetails,[
                'userid'=>'exists:wishare_users,id',
                'offset'=>'numeric',
                'limit'=>'numeric',
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
            else if(isset($failedRules['offset']['Numeric']))
            {
                $message = 'Offset should be numeric.';
                $field = 'offset';
            }
            else if(isset($failedRules['limit']['Numeric']))
            {
                $message = 'Limit should be numeric.';
                $field = 'limit';
            }

            return response()->json(['status'=>'400','message'=>$message,'field'=>$field]);
        }
        else
        {
            $tFriendsId = $this->friend->getUserFriendsId($userid);
            $friendsId[] = (int) $userid;
            $limit = $userDetails['limit'];

            foreach($tFriendsId as $friendId)
            {
                if($friendId['userid'] == $userid)
                    $friendsId[] = $friendId['friend_userid'];
                else
                    $friendsId[] = $friendId['userid'];
            }

            $tstream = $this->wish->getStream($friendsId, $limit);
            $getFavoriteBookmarkForStream = $this->favoritebookmark->getFavoriteBookmarkForStream($userid);

            foreach($tstream as $ts)
            {
                $ts->favorited = '0';
                $ts->bookmarked = '0';
                foreach($getFavoriteBookmarkForStream as $gfbfs)
                {
                    if($ts->wishid == $gfbfs['wishid'])
                    {
                        if($gfbfs['type'] == '2')
                        {
                            $ts->favorited  = '1';
                        }
                        if($gfbfs['type'] == '1')
                        {
                            $ts->bookmarked = '1';
                        }
                    }
                }
                $ts->favoritecount = $this->favoritebookmark->countFavoriteBookmark($ts->wishid, '2');
                $ts->bookmarkcount = $this->favoritebookmark->countFavoriteBookmark($ts->wishid, '1');
            }

            $tstream->setPath('http://'.$this->hostUrl.'/v1/users/'.$userid.'/stream');
            $tstream = $tstream->toArray();
            $stream = $tstream['data'];
            $lastpage = $tstream['last_page'];

            return response()->json(['status'=>'200','message'=>'Ok','lastpage'=>$lastpage,'stream'=>$stream]);
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
    public function store(Request $request)
    {
        //
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
    public function destroy($id)
    {
        //
    }
}
