<?php

namespace App\Http\Controllers\Api;


use Exception;
use App\Models\User;
use App\Models\MentorInformation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\StudentInformation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

//Notification
use App\Notifications\SendNotification;
use Illuminate\Support\Facades\Notification;

class AuthController extends Controller
{

    public function registerUser(Request $request)
    {
        try {
            $validateUser = Validator::make($request->all(), 
            [
                'name' => 'required',
                'organization_slug' => 'required',
                'username' => 'required|unique:users',
                'password' => 'required'
            ]);

            if($validateUser->fails()){
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'data' => $validateUser->errors()
                ], 422);
            }

            if($request->email){
                $is_exist = User::where('email', $request->email)->first();
                if (!empty($is_exist)){
                    return response()->json([
                        'status' => true,
                        'message' => 'Email address already been used! Please use another email',
                        'data' => []
                    ], 422);
                }
            }

            if($request->contact_no){
                $is_exist = User::where('contact_no', $request->contact_no)->first();
                if (!empty($is_exist)){
                    return response()->json([
                        'status' => true,
                        'message' => 'Contact No already been used! Please use another number',
                        'data' => []
                    ], 422);
                }
            }

            $profile_image = null;
            $profile_url = null;
            if($request->hasFile('image')){
                $image = $request->file('image');
                $time = time();
                $profile_image = "profile_image_" . $time . '.' . $image->getClientOriginalExtension();
                $destinationProfile = 'uploads/profile';
                $image->move($destinationProfile, $profile_image);
                $profile_url = $destinationProfile . '/' . $profile_image;
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'username' => $request->username,
                'contact_no' => $request->contact_no,
                'organization_slug' => $request->organization_slug,
                'address' => $request->address,
                'user_type' => $request->user_type ? $request->user_type : "Student",
                'password' => Hash::make($request->password)
            ]);

            
            if($request->user_type == 'Expert'){
                $this->insertMentor($request, $user->id, $profile_url);
            }

            if($request->user_type == 'Student'){
                $this->insertStudent($request, $user->id, $profile_url);
            }

            if($request->hasFile('image')){
                User::where('id', $user->id)->update([
                    'image' => $profile_url
                ]);
            }

            $response_user = [
                'name' => $user->name, 
                'username'=> $user->username, 
                'interests' => [],
                'user_type' => $request->user_type ? $request->user_type : "Student", 
                'token' => $user->createToken("API TOKEN")->plainTextToken
            ];

            return response()->json([
                'status' => true,
                'message' => 'Registration Successful',
                'data' => $response_user
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
                'data' => []
            ], 500);
        }
    }

    // Mentor Registration
    public function insertMentor(Request $request, $user_id, $profile_url){
        try {
            MentorInformation::create([
                'name' => $request->name,
                'email' => $request->email,
                'contact_no' => $request->contact_no,
                'username' => $request->username,
                'organization_slug' => $request->organization_slug,
                'address' => $request->address,
                'image' => $profile_url,
                'user_id' => $user_id
            ]);
            return true;

        } catch (\Throwable $th) {
            return false;
        }
    }

    // Student Registration
    public function insertStudent(Request $request, $user_id, $profile_url){
        try {
            StudentInformation::create([
                'name' => $request->name,
                'email' => $request->email,
                'contact_no' => $request->contact_no,
                'organization_slug' => $request->organization_slug,
                'username' => $request->username,
                'address' => $request->address,
                'image' => $profile_url,
                'user_id' => $user_id
            ]);
            return true;

        } catch (\Throwable $th) {
            return false;
        }
    }

    public function loginUser(Request $request)
    {
        try {
            $validateUser = Validator::make($request->all(), 
            [
                'username' => 'required',
                'password' => 'required'
            ]);

            if($validateUser->fails()){
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'data' => $validateUser->errors()
                ], 401);
            }

            if(!Auth::attempt($request->only(['username', 'password']))){
                return response()->json([
                    'status' => false,
                    'message' => 'Username & Password does not match with our record.',
                    'data' => []
                ], 401);
            }

            $user = User::where('username', $request->username)->first();

            if(!$user->is_active){
                return response()->json([
                    'status' => false,
                    'message' => 'Your account has been suspended. Please, contact to Administrator!',
                    'data' => []
                ], 401);
            }

            Log::debug('Auth Service: Login Successfull! ' . $user->name);

            //Notification::send($user, new SendNotification('Auth Service: Login Successfull! ' . $user->name));

            $interests = [];
            if($user->user_type == 'Student'){
                $interest = StudentInformation::where('user_id', $user->id)->first();
                if($interest->interests){
                    $interests = explode(",",$interest->interests);
                }
            }

            $response_user = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'username' => $user->username,
                'interests' => $interests,
                'user_type' => $user->user_type,
                'image' => $user->image,
                'address' => $user->address,
                'contact_no' => $user->contact_no,
                'updated_at' => $user->updated_at,
                'token' => $user->createToken("API TOKEN")->plainTextToken
            ];

            return response()->json([
                'status' => true,
                'message' => 'User Logged In Successfully',
                'data' => $response_user
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
                'data' => []
            ], 500);
        }
    }

    public function profileDetailsByID($user_id)
    {
        $user = User::select('users.*', 'countries.country_name')->where('users.id', $user_id)
        ->leftJoin('countries', 'countries.id', 'users.country_id')
        ->first();

        return $user;
    }

    public function saveOrUpdateUser(Request $request)
    {
        try {
            $formData = json_decode($request->data, true);
            if($formData['id']){
                $profile_url = null;
                if($request->hasFile('file')){
                    $image = $request->file('file');
                    $time = time();
                    $feature_image = "profile_image_" . $time . '.' . $image->getClientOriginalExtension();
                    $destinationProfile = 'uploads/profile';
                    $image->move($destinationProfile, $feature_image);
                    $profile_url = $destinationProfile . '/' . $feature_image;
                }

                User::where('id', $formData['id'])->update([
                    'name' => $formData['name'],
                    'email' => $formData['email'],
                    'contact_no' => $formData['contact_no'],
                    'country_id' => $formData['country_id'],
                    'address' => $formData['address'],
                    'institution' => $formData['institution'],
                    'education' => $formData['education'],
                    'user_type' => $formData['user_type'] ? $formData['user_type'] : "Student"
                ]);

                if($request->hasFile('file')){
                    User::where('id', $formData['id'])->update([
                        'image' => $profile_url
                    ]);
                }

                return response()->json([
                    'status' => true,
                    'message' => 'User has been updated successfully',
                    'data' => []
                ], 200);

            } else {
                $isExist = User::where('email', $formData['email'])->first();
                if (empty($isExist)) 
                {
                    $profile_url = null;
                    if($request->hasFile('file')){
                        $image = $request->file('file');
                        $time = time();
                        $feature_image = "profile_image_" . $time . '.' . $image->getClientOriginalExtension();
                        $destinationProfile = 'uploads/profile';
                        $image->move($destinationProfile, $feature_image);
                        $profile_url = $destinationProfile . '/' . $feature_image;
                    }

                    $user = User::create([
                        'name' => $formData['name'],
                        'email' => $formData['email'],
                        'contact_no' => $formData['contact_no'],
                        'country_id' => $formData['country_id'],
                        'address' => $formData['address'],
                        'institution' => $formData['institution'],
                        'education' => $formData['education'],
                        'user_type' => $formData['user_type'] ? $formData['user_type'] : "Student"
                    ]);

                    if($request->hasFile('file')){
                        User::where('id', $user->id)->update([
                            'image' => $profile_url
                        ]);
                    }

                    return response()->json([
                        'status' => true,
                        'message' => 'User has been added successfully',
                        'data' => []
                    ], 200);
                }else{
                    return response()->json([
                        'status' => false,
                        'message' => 'User already Exist!',
                        'data' => []
                    ], 200);
                }
            }

        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'data' => []
            ], 200);
        }
    }

    public function updateInterest(Request $request){
        $user_id = $request->user()->id;

        if(sizeof($request->interests)){
            return response()->json([
                'status' => false,
                'message' => 'Check Details',
                'data' => []
            ], 409);
        }

        $user = User::where('id', $user_id)->first();
        
        if($user->user_type == "Student"){
            $student = StudentInformation::where('user_id', $user_id)->first();
            $interest_list = explode(',',$student->interests);

            foreach ($request->interests as $item) {
                if (!in_array($item, $interest_list)){
                    array_push($interest_list, $item); 
                }
            }

            $student->update([
                'interests' => implode(',', $interest_list)
            ]);
        }

        $response_user = [
            'name' => $user->name, 
            'username'=> $user->username, 
            'interests' => explode(',',$student->interests),
            'user_type' => $user->user_type, 
            'token' => $user->createToken("API TOKEN")->plainTextToken
        ];

        return response()->json([
            'status' => true,
            'message' => 'Registration Successful',
            'data' => $response_user
        ], 200);
        
    }

    public function updateUser(Request $request)
    {
        $user_id = $request->user()->id;
        try {
            if(!$request->name && !$request->contact_no && !$request->country_id && !$request->address && !$request->institution && !$request->education && !$request->hasFile('image')){
                return response()->json([
                    'status' => false,
                    'message' => 'Please, attach information!',
                    'data' => []
                ], 200);
            }

            $profile_image = null;
            $profile_url = null;
            if($request->hasFile('image')){
                $image = $request->file('image');
                $time = time();
                $profile_image = "profile_image_" . $time . '.' . $image->getClientOriginalExtension();
                $destinationProfile = 'uploads/profile';
                $image->move($destinationProfile, $profile_image);
                $profile_url = $destinationProfile . '/' . $profile_image;
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'contact_no' => $request->contact_no,
                'address' => $request->address,
            ]);

            if($request->name){
                User::where('id', $user_id)->update([
                    'name' => $request->name
                ]);
            }

            if($request->contact_no){
                User::where('id', $user_id)->update([
                    'contact_no' => $request->contact_no
                ]);
            }

            if($request->country_id){
                User::where('id', $user_id)->update([
                    'country_id' => $request->country_id
                ]);
            }

            if($request->address){
                User::where('id', $user_id)->update([
                    'address' => $request->address
                ]);
            }

            if($request->institution){
                User::where('id', $user_id)->update([
                    'institution' => $request->institution
                ]);
            }

            if($request->education){
                User::where('id', $user_id)->update([
                    'education' => $request->education
                ]);
            }

            // User::where('id', $user_id)->update([
            //     'name' => $request->name,
            //     'contact_no' => $request->contact_no,
            //     'country_id' => $request->country_id,
            //     'address' => $request->address,
            //     'institution' => $request->institution,
            //     'education' => $request->education
            // ]);

            if($request->hasFile('image')){
                User::where('id', $user_id)->update([
                    'image' => $profile_url
                ]);
            }

            return response()->json([
                'status' => true,
                'message' => 'Updated Successful',
                'data' => $this->profileDetailsByID($user_id)
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
                'data' => []
            ], 500);
        }
    }

    public function getProfile(Request $request)
    {
        $user_id = $request->user()->id;
        $user = User::select('users.*', 'countries.country_name')->where('users.id', $user_id)
        ->leftJoin('countries', 'countries.id', 'users.country_id')
        ->first();

        return response()->json([
            'status' => true,
            'message' => 'Successful',
            'data' => $user
        ], 200);
    }

    public function getExpertList(Request $request)
    {
        $users = User::select('users.id', 'users.name', 'users.email', 'users.contact_no', 'users.address', 'users.education', 'users.institution', 'users.image', 'countries.country_name')
        ->where('users.user_type', 'Expert')
        ->where('users.is_active', true)
        ->leftJoin('countries', 'countries.id', 'users.country_id')
        ->get();

        return response()->json([
            'status' => true,
            'message' => 'Successful',
            'data' => $users
        ], 200);
    }

    public function getAdminExpertList(Request $request)
    {
        $users = User::select('users.*', 'countries.country_name')
        ->where('users.user_type', 'Expert')
        ->leftJoin('countries', 'countries.id', 'users.country_id')
        ->get();

        return response()->json([
            'status' => true,
            'message' => 'Successful',
            'data' => $users
        ], 200);
    }

    public function deleteUserAccount(Request $request)
    {
        $user_id = $request->user()->id;

        $user = User::where('id', $user_id)->first();

        User::where('id', $user_id)->update([
            "contact_no" => $user_id . "_deleted_" . $user->contact_no,
            "email" => $user_id . "_deleted_" . $user->email,
            "is_active" => false
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Account deleted successful',
            'data' => []
        ], 200);
    }
}
