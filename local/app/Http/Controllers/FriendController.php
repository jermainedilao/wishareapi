<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Friend;
use App\WishareUser;
use Validator;

class FriendController extends Controller
{

    protected $friend;
    protected $user;

    public function __construct()
    {
        $this->friend = new Friend();
        $this->user = new WishareUser();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $userid)
    {
        $friendDetails['userid'] = $userid;
        if($request['frienduserid'] != '')
            $friendDetails['frienduserid'] = $request['frienduserid'];
        
        $validation = Validator::make($friendDetails,[
                'userid'=>'exists:wishare_users,id',
                'frienduserid'=>'exists:wishare_users,id'
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
            else if(isset($failedRules['frienduserid']['Exists']))
            {
                $message = 'Friend user id not found.';
                $field = 'frienduserid';
            }

            return response()->json(['status'=>'400','message'=>$message,'field'=>$field]);
        }
        else
        {
            //$users = $this->wishareUser->searchUsers($username);
            
            $friendsId = $this->friend->getUserFriendsId($userid);
            $friends = [];

            foreach($friendsId as $friendId)
            {
                if($friendId['userid'] == $userid)
                    $friends[] = $this->user->getUser($friendId['friend_userid']);
                else
                    $friends[] = $this->user->getUser($friendId['userid']);
            }

            return response()->json(['status'=>'200','message'=>'Ok','friends'=>$friends]);
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
        $friendDetails = array(
                'userid'=>$userid,
                'friend_userid'=>$request['friend_userid']
            );

        $validation = Validator::make($friendDetails, [
                'userid'=>'exists:wishare_users,id',
                'friend_userid'=>'required|exists:wishare_users,id'
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
            else if(isset($failedRules['friend_userid']['Required']))
            {
                $message = 'Friend user id is required.';
                $field = 'friend_userid';
            }
            else if(isset($failedRules['friend_userid']['Exists']))
            {
                $message = 'Friend user id not found.';
                $field = 'friend_userid';
            }

            return response()->json(['status'=>'400','message'=>$message,'field'=>$field]);
        }
        else
        {
            if($this->friend->isUserIdFriendUserIdUnique($friendDetails))
            {
                $addFriendRequest = $this->friend->addFriendRequest($friendDetails);

                if($addFriendRequest != null)
                    return response()->json(['status'=>'201','message'=>'Successfully added user.','friends'=>$addFriendRequest]);
                else            
                    return response()->json(['status'=>'500','message'=>'Internal server error. Please try again.']);
            }
            else
                return response()->json(['status'=>'400','message'=>'Already added as friend.']);
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
    public function update(Request $request, $userid, $frienduserid)
    {
        $friendDetails['userid'] = $userid;
        $friendDetails['friend_userid'] = $frienduserid;
        $friendDetails['action'] = $request['action'];

        $validation = Validator::make($friendDetails,[
                    'action'=>'required|in:accept,decline,cancel'
                ]);

        if($validation->fails())
        {
            $failedRules = $validation->failed();
            $field = '';

            if(isset($failedRules['action']['Required']))
            {
                $message = 'Action is required.';
                $field = 'action';
            }
            else if(isset($failedRules['action']['In']))
            {
                $message = 'Action must be accept, decline, or cancel only';
                $field = 'action';
            }

            return response()->json(['status'=>'400','message'=>$message,'field'=>$field]);
        }
        else
        {
            if($friendDetails['action'] == 'accept' || $friendDetails['action'] == 'decline')
            {
                $validation = Validator::make($friendDetails,[
                        'userid'=>'exists:friends,friend_userid',
                        'friend_userid'=>'exists:friends,userid',
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
                    else if(isset($failedRules['friend_userid']['Exists']))
                    {
                        $message = 'Friend user id not found.';
                        $field = 'frienduserid';
                    }

                    return response()->json(['status'=>'400','message'=>$message,'field'=>$field]);
                }
                else
                {
                    if($friendDetails['action'] == 'accept')
                    {
                        $acceptFriendRequest = $this->friend->acceptFriendRequest($friendDetails);

                        if($acceptFriendRequest)
                            return response()->json(['status'=>'200','message'=>'Accepted friend request.']);
                        else
                            return response()->json(['status'=>'400','message'=>'Mismatched userids.']);
                    }
                    else if($friendDetails['action'] == 'decline')
                    {
                        $declineFriendRequest = $this->friend->declineFriendRequest($friendDetails);

                        if($declineFriendRequest)
                            return response()->json(['status'=>'200','message'=>'Declined friend request.']);
                        else
                            return response()->json(['status'=>'400','message'=>'Mismatched userids.']);
                    }
                    else
                        return response()->json(['status'=>'500','message'=>'Internal server error. Please try again.']);
                }
            }
            else if($friendDetails['action'] == 'cancel')
            {
                $validation = Validator::make($friendDetails,[
                        'userid'=>'exists:friends,userid',
                        'friend_userid'=>'exists:friends,friend_userid',
                    ]);

                if($validation->fails())
                {
                    $failedRules = $validation->failed();
                    $field = '';

                    if(isset($failedRules['userid']['Exists']))
                    {
                        $message = 'Userid not found.';
                        $field = 'userid';
                    }
                    else if(isset($failedRules['friend_userid']['Exists']))
                    {
                        $message = 'Friend user id not found.';
                        $field = 'frienduserid';
                    }

                    return response()->json(['status'=>'400','message'=>$message,'field'=>$field]);
                }
                else
                {
                    $cancelFriendRequest = $this->friend->cancelFriendRequest($friendDetails);

                    if($cancelFriendRequest)
                        return response()->json(['status'=>'200','message'=>'Cancelled friend request.']);
                    else
                        return response()->json(['status'=>'400','message'=>'Mismatched userids.']);
                }
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($userid, $frienduserid)
    {
        $friendDetails['userid'] = $userid;
        $friendDetails['friend_userid'] = $frienduserid;

        $validation = Validator::make($friendDetails, [
                'userid'=>'exists:friends,userid',
                'friend_userid'=>'exists:friends,friend_userid'
            ]);

        $validationSuccess = true;

        if($validation->fails())
        {
            $validationSuccess = false;

            $validation = Validator::make($friendDetails, [
                'friend_userid'=>'exists:friends,userid',
                'userid'=>'exists:friends,friend_userid'
            ]);

            if($validation->fails())
            {
                $failedRules = $validation->failed();
                $field = '';

                if(isset($failedRules['friend_userid']['Exists']))
                {
                    $message = 'Friend user id not found.';
                    $field = 'frienduserid';
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
                $validationSuccess = true;
            }
        }

        if($validationSuccess)
        {
            $unfriendFriend = $this->friend->unfriendFriend($friendDetails);

            if($unfriendFriend)
                return response()->json(['status'=>'200','message'=>'Unfriended friend.']);
            else
                return response()->json(['status'=>'400','message'=>'Unmatched ids.']);
        }
    }

    public function getPendingFriendRequest($userid)
    {
        $userDetails['id'] = $userid;

        $validation = Validator::make($userDetails, [
                'id'=>'exists:wishare_users,id'
            ]);

        if($validation->fails())
        {
            $failedRules = $validation->failed();
            $field = '';

            if(isset($failedRules['id']['Exists']))
            {
                $message = 'Id not found.';
                $field = 'userid';
            }

            return response()->json(['status'=>'400','message'=>$message,'field'=>$field]);
        }
        else
        {
            $getPendingFriendRequest = $this->friend->getPendingFriendRequest($userid);
            return response()->json(['status'=>'200','message'=>'Ok','pendingfriendrequests'=>$getPendingFriendRequest]);
        }

    }
}
