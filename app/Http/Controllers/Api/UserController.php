<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\User;
use App\Notifications\CommonNotification;
use App\Traits\ResetsPasswords;
use App\Traits\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use MongoDB\Operation\Update;

class UserController extends Controller
{
    use SendsPasswordResetEmails, ResetsPasswords;
    public function __construct()
    {
        $this->broker = 'users';
    }

    /**
     * Handles Registration Request
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $email = strtolower($request->email);
        $validator = Validator::make($request->input(), [
            'username' => 'required|min:3',
            'email' => 'required|email',
            'password' => 'required|min:6'
        ]);
        if ($validator->fails()) {
            return response(['errors' => $validator->errors()->all()], 422);
        }
        $check_existing = $this->checkUsingEmailOrUserName(['email' => $email, 'username' => $request->username]);
        if (!empty($check_existing)) {
            $msg = '';
            if ($check_existing->username == $request->username)
                $msg = 'username already exists';
            if ($check_existing->email == $email)
                $msg = 'email already exists';
            return response()->json(['status' => 200, 'message' => $msg]);
        }
        $user = User::create([
            'username'       => $request->username,
            'email'          => $email,
            'password'       => Hash::make($request->password),
            'active'         => 0,
            'verify_account' => Str::random(10),
            'first_login'    => 1,
            'user_type_id'   => 2, // normal users
            'is_deleted'     => 0
        ]);

        // $token = $user->createToken('LinkMe')->accessToken;

        $this->sendVerifyEmail($user);
        // return response()->json(['token' => $token], 200);
        return response()->json(['message' => 'Activate your Account using activation link.'], 200);
    }

    /**
     * Handles Login Request
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $email = $request->email;
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:6',
        ]);
        if ($validator->fails()) {
            return response(['errors' => $validator->errors()->all()], 422);
        }
        $user = User::where('email', $email)->where('is_deleted', 0)->first();
        if ($user) {
            if (Hash::check($request->password, $user->password) && isset($user->active) && !empty($user->active)) {
                $token = $user->createToken(env('APP_NAME'))->accessToken;
                $first_login = (isset($user->first_login)) ? $user->first_login : 0;
                $response = ['token' => $token, 'first_login' => $first_login, 'username' => $user->username];
                return response($response, 200);
            } else if (!isset($user->active) || empty($user->active)) {
                $response = ["message" => "Your Account is Not Active Yet."];
                return response($response, 422);
            } else {
                $response = ["message" => "Password mismatch"];
                return response($response, 422);
            }
        } else {
            $response = ["message" => 'User does not exist'];
            return response($response, 422);
        }
    }

    /**
     * Returns Authenticated User Details
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function details()
    {
        return response()->json(['user' => auth()->user()], 200);
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            Auth::user()->token()->revoke();
            return response()->json(['success' => 'logout_success'], 200);
        } else {
            return response()->json(['error' => 'api.something_went_wrong'], 500);
        }
    }

    public function changePassword(Request $request)
    {
        $userid = auth()->user()->id;
        $input = $request->input();
        $rules = array(
            'old_password' => 'required',
            'new_password' => 'required|min:6',
            'confirm_password' => 'required|same:new_password',
        );
        $validator = Validator::make($input, $rules);
        if ($validator->fails()) {
            $arr = array("status" => 400, "message" => $validator->errors()->first(), "data" => array());
        } else {
            if ((Hash::check(request('old_password'), Auth::user()->password)) == false) {
                $arr = array("status" => 400, "message" => "Check your old password.", "data" => array());
            } else if ((Hash::check(request('new_password'), Auth::user()->password)) == true) {
                $arr = array("status" => 400, "message" => "Please enter a password which is not similar then current password.", "data" => array());
            } else {
                User::where('_id', $userid)->update(['password' => Hash::make($input['new_password'])]);
                $arr = array("status" => 200, "message" => "Password updated successfully.", "data" => array());
            }
        }
        return $arr;
    }

    public function verifyAccount(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        if (!empty($user)) {
            if ($user->verify_account == $request->verify_token) {
                $user->unset('verify_account');
                $user->active = 1;
                $user->save();
                return response()->json(['message' => 'Account Activated Successfully.', 'activation_status' => $user->active], 200);
            } else {
                return response()->json(['message' => 'Code mismatch please try again.', 'activation_status' => $user->active], 200);
            }
        } else {
            return response()->json(['message' => 'No user found.'], 200);
        }
    }

    public function resendVerifyEmail(Request $request)
    {
        $user = User::where('email', $request->email)->where('active', 0)->first();
        if (!empty($user)) {
            $this->sendVerifyEmail($user);
            return response()->json(['message' => 'Mail has been sent.'], 200);
        } else {
            return response()->json(['message' => 'No user found.'], 200);
        }
    }

    public function sendVerifyEmail(object $user)
    {
        $notify = [
            'email_subject'     => 'Account Activation',
            'introduction_line' => "Thank you for registering on " . env('APP_NAME') . '.',
            'email_text'        => 'You need to activate your account in order to use our service. please click link we sent on your email address.',
            'action_text'       => 'Activate Your Account',
            'action_url'        => env('FRONT_END_LINK', 'http://localhost:3100/') . 'user/verify_account?email=' . $user->email . '&verify_token=' . $user->verify_account,
            'email_blade'       => 'mail.default'
        ];
        Notification::send($user, new CommonNotification($notify));
        return true;
    }

    public function setupUser(Request $request)
    {
        $input = $request->input();
        $rules = array(
            'name' => 'required|min:3',
        );
        $update = User::find(auth()->user()->id);
        $arr = [];
        $validator = Validator::make($input, $rules);
        if ($validator->fails()) {
            $arr = array("status" => 400, "message" => $validator->errors()->first());
        } else {
            // validate email and username
            $check_email = $this->checkUsingEmailOrUserName(['email' => $request->email, 'username' => $request->username]);
            if (!empty($check_email) && $check_email->_id === $update->_id) {
                $arr = array("status" => 400, "message" => 'Email Or username Already exists');
                return json_encode($arr);
            }

            if ($request->filled('name')) {
                $update->name = $request->name;
            }
            if ($request->filled('categories')) {
                $update->categories = $request->categories;
            }
            if ($request->filled('email')) {
                $update->email = $request->email;
            }
            if ($request->filled('username')) {
                $update->username = $request->username;
            }
            if ($request->filled('is_deleted')) {
                $update->is_deleted = $request->is_deleted;
            }
            $update->first_login = 0;
            $update->save();
            $arr = array("status" => 200, "message" => 'information Saved');
        }
        return json_encode($arr);
    }

    public function getCategories(Request $request)
    {
        $categories = Category::get();
        return json_encode(['status' => 200, 'categories' => $categories]);
    }

    public function migration(Request $request)
    {
        $users = User::get();
        foreach ($users as $key => $user) {
            Log::info($user);
            /* if(empty($user->username)){
                $saveUser = User::find($user->id);
                $saveUser->username = Str::random(3);
                $saveUser->save();
            } */
        }
    }

    public function indexerror(Request $request)
    {
        $return_data = array();
        if ($request->filled('indate'))
            $indate = $request->indate;
        else
            $indate = date("Y-m-d");
        $filedata = File::get(storage_path() . '/logs/lumen-' . $indate . '.log');
        $return_data["indate"]         = $indate;
        $return_data["filedata"]       = "<xmp>" . $filedata . "</xmp>";
        $return_data["site_title"]     = "Error Log";
        $return_data['page_condition'] = '';
        return view('error_log', $return_data);
    }

    public function checkUsingEmailOrUserName(array $params)
    {
        $email    = ((isset($params['email'])) ? $params['email'] : '');
        $username = ((isset($params['username'])) ? $params['username'] : '');

        $getUser = User::where('email', $email)
            ->when((isset($email) && !empty($email)), function ($query) use ($email) {
                return $query->where('email', $email);
            })
            ->when((isset($username) && !empty($username)), function ($query) use ($username) {
                return $query->orWhere('username', $username);
            })->first();
        return $getUser;
    }

    public function saveProfile(Request $request)
    {
        $username    = $request->username;
        $user        = User::where('username', $username)->first();
        if (!empty($user)) {
            $validator = Validator::make($request->input(), [
                'profile_name'  => 'min:3|max:20',
                'bio'           => 'max:120',
                'cover_image'   => 'mimes:jpg,jpeg,png',
                'profile_image' => 'mimes:jpg,jpeg,png'
            ],
            [
                'profile_name.min' => 'Profile name length must be greater than 3 characters.',
                'profile_name.max' => 'Profile name length must be less than 20 characters.',
                'bio.max'   => 'Profile bio can\'t be more than 120 characters Long.',
                'cover_image' => 'Must be Image file',
                'profile_image' => 'Must be Image file',
            ]);
            if ($validator->fails()) {
                return response(['errors' => $validator->errors()->all()], 422);
            }

            $save_params = $request->input();
            // save user files
            if ($request->hasFile('cover_image')) {
                $image                      = $request->file('cover_image');
                $cover_image                  = time() . uniqid() . '.' . $image->getClientOriginalExtension();
                $image->storeAs('public', $cover_image);
                $save_params['cover_image'] = $cover_image;
            }
            if ($request->hasFile('profile_image')) {
                $image                      = $request->file('profile_image');
                $profile_image                  = time() . uniqid() . '.' . $image->getClientOriginalExtension();
                $image->storeAs('public', $profile_image);
                $save_params['profile_image'] = $profile_image;
            }
            $usrobj = new User();
            $usrobj->saveUserDetails($user, $save_params);
            $arr = array("status" => 200, "message" => 'information Saved');
        } else {
            $arr = array("status" => 200, "message" => 'User Not found');
        }
        return json_encode($arr);
    }
}
