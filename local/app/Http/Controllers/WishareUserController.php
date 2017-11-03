<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Validator;
use App\WishareUser;
use App\Friend;
use App\Wishlist;
use App\Wish;
use App\FavoriteBookmark;

class WishareUserController extends Controller
{

    protected $wishareUser;
    protected $friend;
    protected $wishlist;
    protected $wish;
    protected $favoritebookmark;
    protected $hostUrl = 'images.wishare.net';

    public function __construct()
    {
        $this->wishareUser = new WishareUser();
        $this->friend = new Friend();
        $this->wishlist = new Wishlist();
        $this->wish = new Wish();
        $this->favoritebookmark = new FavoriteBookmark();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
    public function show(Request $request, $id)
    {
        $userDetails['loggeduserid'] = $request['loggeduserid'];

        $validation = Validator::make($userDetails,[
                'loggeduserid'=>'exists:wishare_users,id'
            ]);

        if($validation->fails())
        {
            $failedRules = $validation->failed();
            $field = '';

            if(isset($failedRules['loggeduserid']['Exists']))
            {
                $message = 'Logged user id not found.';
                $field = 'loggeduserid';          
            }

            return response()->json(['status'=>'400','message'=>$message,'field'=>$field]);
        }
        else
        {
            $userId = $id;
            $user = $this->wishareUser->getUser($userId);
        
            if($user == null)
            {
                $message = 'Id doesn\'t exist.';
                return response()->json(['status'=>'400','message'=>$message]);
            }
            else
            {
                $user['countwishlists'] = $this->wishlist->countUserWishlist($id);
                $user['countwishes'] = $this->wish->countUserWish($id);
                $user['countgranted'] = $this->wish->countUserGranted($id);
                $user['countgiven'] = $this->wish->countUserGiven($id);
                $user['countfriends'] = $this->friend->countUserFriend($id);
                $user['counttracked'] = $this->favoritebookmark->countUserBookmark($id);

                if($request['loggeduserid'] != '')
                    $user['isfriend'] = $this->friend->checkIfFriends($userId, $request['loggeduserid']);

                $message = 'Ok';
                return response()->json(['status'=>'200','message'=>$message,'user'=>$user]);
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
    public function update(Request $request, $id)
    {
        $userDetails = array();

        if($request['lastname'] != '')
            $userDetails['lastname'] = $request['lastname'];
        if($request['firstname'] != '')
            $userDetails['firstname'] = $request['firstname'];
        if($request['username'] != '')
            $userDetails['username'] = $request['username'];
        if($request['password'] != '')
        {
            $userDetails['password'] = $request['password'];
            if($request['oldpassword'] != '')
                $oldpassword = $request['oldpassword'];
            else
                return response()->json(['status'=>'400','message'=>'Old password is required.','field'=>'oldpassword']);
        }
        if($request['email'] != '')
            $userDetails['email'] = $request['email'];
        if($request['facebook'] != '')
            $userDetails['facebook'] = $request['facebook'];
        if($request['birthdate'] != '')
            $userDetails['birthdate'] = $request['birthdate'];
        if($request['privacy'] != '')
            $userDetails['privacy'] = $request['privacy'];

        $validation = Validator::make($userDetails, [
                'lastname'=>'min:2|max:50|regex:/^([a-zA-Z]+\s)*[a-zA-Z]+$/',
                'firstname'=>'min:2|max:50|regex:/^([a-zA-Z]+\s)*[a-zA-Z]+$/',
                'username'=>'min:3|max:15|unique:wishare_users|alpha_num',
                'password'=>'min:3|max:30|alpha_num',
                'email'=>'unique:wishare_users|max:50|email',
                'privacy'=>'in:1,0'
            ]);

        if($validation->fails())
        {
            $failedRules = $validation->failed();
            $field = '';
            
            if(isset($failedRules['lastname']['Regex'])){
                $message = 'Lastname must be letters only.';
                $field = 'lastname';
            }
            else if(isset($failedRules['lastname']['Max'])){
                $message = 'Lastname must not exceed 50 letters.';
                $field = 'lastname';
            }
            else if(isset($failedRules['lastname']['Min'])){
                $message = 'Lastname must be atleast 2 letters.';
                $field = 'lastname';
            }
            else if(isset($failedRules['firstname']['Regex'])){
                $message = 'Firstname must be letters only.';
                $field = 'firstname';
            }
            else if(isset($failedRules['firstname']['Max'])){
                $message = 'Firstname must not exceed 50 letters.';
                $field = 'firstname';
            }
            else if(isset($failedRules['firstname']['Min'])){
                $message = 'Firstname must be atleast 2 letters.';
                $field = 'firstname';
            }
            else if(isset($failedRules['username']['AlphaNum'])){
                $message = 'Username can only contain letters and numbers.';
                $field = 'username';
            }
            else if(isset($failedRules['username']['Min'])){
                $message = 'Username must be atleast 3 characters.';
                $field = 'username';
            }
            else if(isset($failedRules['username']['Max'])){
                $message = 'Username must not exceed 15 characters.';
                $field = 'username';
            }
            else if(isset($failedRules['password']['AlphaNum'])){
                $message = 'Password can only contain letters and numbers.';
                $field = 'password';
            }
            else if(isset($failedRules['password']['Min'])){
                $message = 'Password must be atleast 3 characters.';
                $field = 'password';
            }
            else if(isset($failedRules['password']['Max'])){
                $message = 'Password must not exceed 30 characters.';
                $field = 'password';
            }
            else if(isset($failedRules['username']['Unique'])){
                $message = 'Username is already taken.';
                $field = 'username';
            }
            else if(isset($failedRules['email']['Email'])){
                $message = 'Please enter a valid email.';
                $field = 'email';
            }
            else if(isset($failedRules['email']['Unique'])){
                $message = 'Email is already taken.';
                $field = 'email';
            }
            else if(isset($failedRules['privacy']['In']))
            {
                $message = 'Privacy must be only 1 or 0.';
                $field = 'privacy';
            }

            return response()->json(['status'=>'400', 'field'=>$field,'message'=>$message]);
        }
        else
        {       
            if(isset($oldpassword) && $oldpassword != '')    
            {
                $isOldPasswordCorrect = $this->wishareUser->isOldPasswordCorrect($id, $oldpassword);
                
                if(!$isOldPasswordCorrect)
                    return response()->json(['status'=>'400','message'=>'Incorrect old password.','field'=>'oldpassword']);
            }

            $user = $this->wishareUser->updateUser($userDetails, $id);
            if($user == null)
            {
                return response()->json(['status'=>'400','message'=>'Id doesn\'t exist.']);
            }
            else
            {
                if($request['image'] != '')
                {
                    $userId = $id;
                    //IMAGE PATH FOR SERVER
                    $userImagePath = "/var/www/images.wishare.net/public_html/wishareimages/userimages/".$userId.time().".jpeg";
                    //put image to directory
                    //IMAGE PATH FOR LOCAL
                    // $userImagePath = "C:/xampp/htdocs/wishareimages/userimages/".$userId.time().".jpeg";
                    file_put_contents($userImagePath, base64_decode($request['image']));
                    $imageUrl = "http://".$this->hostUrl."/userimages/".$userId.time().".jpeg";
                    $user = $this->wishareUser->addImageUrl($id, $imageUrl);
                }

                return response()->json(['status'=>'200','message'=>'User updated.','user'=>$user]);
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
        //
    }

    public function search(Request $request, $id)
    {
        $userDetails['id'] = $id;
        $username = $request['username'];

        $validation = Validator::make($userDetails, [
                'id'=>'exists:wishare_users,id'
            ]);

        if($validation->fails())
        {
            $failedRules = $validation->failed();
            $field = '';

            if(isset($failedRules['id']['Exists']))
            {
                $message = 'User id doesn\'t exist.';
                $field = 'id';
            }

            return response()->json(['status'=>'400', 'message'=>$message, 'field'=>$field]);
        }
        else
        {
            $users = $this->wishareUser->searchUsers($username);
            $friends = $this->friend->getUsersIdInFriendsTable($id);

            foreach($users as $user)
            {
                if(count($friends) > 0)
                {
                    foreach($friends as $friend)
                    {
                        if($friend['friend_userid'] == $user['id'])
                        {
                            if($friend['status'] == '1')
                                $user['isfriend'] = 'true';
                            else
                                $user['isfriend'] = 'pendingrequest';    
                            break;
                        }
                        else if($friend['userid'] == $user['id'])
                        {
                            if($friend['status'] == '1')
                                $user['isfriend'] = 'true';
                            else
                                $user['isfriend'] = 'pendingaccept';    
                            break;
                        }
                        else
                            $user['isfriend'] = 'false';
                    }
                }
                else
                    $user['isfriend'] = 'false';
            }

            return response()->json(['status'=>'200','message'=>'Ok','users'=>$users]);
        }
    }
}
