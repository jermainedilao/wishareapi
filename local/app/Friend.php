<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Friend extends Model
{
    protected $fillable = [
    					'friend_userid',
    					'userid',
    					'date_added',
    					'date_accepted',
    					'status'
    				];

	public function getUsersIdInFriendsTable($userid)
	{
		$friends = Friend::where('userid','=',$userid)
                   ->orWhere('friend_userid','=',$userid)
                   ->get();
		return $friends;
	}

    public function addFriendRequest($friendDetails)
    {
        $friendDetails['status'] = '0';
        $addFriendRequest = Friend::create($friendDetails);

        return $addFriendRequest;
    }

    public function isUserIdFriendUserIdUnique($friendDetails)
    {
        $isUnique = Friend::where('friend_userid','=',$friendDetails['friend_userid'])
                    ->where('userid','=',$friendDetails['userid'])
                    ->count();

        if($isUnique > 0)
            return false;
        else
        {
            $isUnique = Friend::where('friend_userid','=',$friendDetails['userid'])
                        ->where('userid','=',$friendDetails['friend_userid'])
                        ->count();
            if($isUnique >0)
                return false;
            else
                return true;
        }
    }

    public function countUserFriend($id)
    {
        $getFriends = Friend::where('userid','=',$id)
                        ->orWhere('friend_userid','=',$id)
                        ->get();
        $countFriends = 0;

        foreach($getFriends as $friend)
        {
            if($friend['status'] == '1')
                $countFriends++;
        }

        return $countFriends;
    }

    public function acceptFriendRequest($friendDetails)
    {
        $acceptFriendDetails['status'] = '1';
        $acceptFriendRequest = Friend::where('friend_userid','=',$friendDetails['userid'])
                               ->where('userid','=',$friendDetails['friend_userid'])
                               ->update($acceptFriendDetails);

        return $acceptFriendRequest;
    }

    public function declineFriendRequest($friendDetails)
    {
        $declineFriendRequest = Friend::where('friend_userid','=',$friendDetails['userid'])
                                ->where('userid','=',$friendDetails['friend_userid'])
                                ->delete();
        return $declineFriendRequest;
    }

    public function cancelFriendRequest($friendDetails)
    {
        $cancelFriendRequest = Friend::where('userid','=',$friendDetails['userid'])
                               ->where('friend_userid','=',$friendDetails['friend_userid'])
                               ->delete();
        return $cancelFriendRequest;
    }

    public function unfriendFriend($friendDetails)
    {
        $unfriendFriend = Friend::where('userid','=',$friendDetails['userid'])
                          ->where('friend_userid','=',$friendDetails['friend_userid'])
                          ->orWhere('userid','=',$friendDetails['friend_userid'])
                          ->where('friend_userid','=',$friendDetails['userid'])
                          ->delete();

        return $unfriendFriend;
    }

    public function getPendingFriendRequest($userid)
    {
        $getPendingFriendRequest = DB::table('wishare_users')
                                   ->leftJoin('friends','wishare_users.id','=','friends.userid')
                                   ->select('wishare_users.id', 'wishare_users.username', 'wishare_users.privacy','wishare_users.imageurl')
                                   ->where('friends.friend_userid','=',$userid)
                                   ->where('friends.status','=','0')
                                   ->orderBy('friends.created_at', 'desc')
                                   ->get();

        return $getPendingFriendRequest;
    }

    public function getUserFriends($friendDetails)
    {
        if($friendDetails['friend_userid'] != '')
        {

        }
        else
        {
            /*$getUserFriends = DB::table('wishare_users')
                              ->leftJoin('friends','wishare_users.id','=','friends.userid')
                              ->leftJoin('friends','wishare_users.id','=','friends.friend_userid')
                              ->select('wishare_users.id','wishare_users.username','wishare_users.privacy','wishare_users.imageurl')
                              ->where('friends.friend_userid','=',$friendDetails['userid'])
                              ->orWhere('friends.userid','=',$friendDetails['friend_userid'])*/
        }
    }

    public function getUserFriendsId($userid)
    {
        $friends = Friend::where('status','=','1')
                   ->where(function($query)use($userid)
                   {
                        $query->where('userid','=',$userid)
                        ->orWhere('friend_userid','=',$userid);
                   })
                   ->get();
        return $friends;
    }

    public function checkIfFriends($userid, $loggeduserid)
    {
        $checkIfFriend = Friend::where('friend_userid','=',$loggeduserid)
                         ->where('userid','=',$userid)
                         ->get();

        if(count($checkIfFriend) > 0)
        {
            foreach($checkIfFriend as $check)
            {    
                if($check['status'] == '1')
                    return "true";
                else
                    return "pendingaccept";
            }
        }
        else
        {
            $checkIfFriend = Friend::where('friend_userid','=',$userid)
                         ->where('userid','=',$loggeduserid)
                         ->get();    
            if(count($checkIfFriend) > 0)
            {
                foreach($checkIfFriend as $check)
                {
                    if($check['status'] == '1')
                        return "true";
                    else
                        return "pendingrequest";
                }
            }
            else
                return "false";

        }

        /*if(count($checkIfFriend) > 0)
            return "not empty";
        else
            return "empty";*/

        return $checkIfFriend;
    }
}
