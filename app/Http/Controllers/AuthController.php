<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Log;

class AuthController extends Controller
{
    public function createUser(Request $request)
    {
        try {
            // MULTIPLE VALIDATION FIELDS
            $validateUser = Validator::make(
                $request->all(),
                [
                    'first_name' => 'required|string|max:255',
                    'last_name' => 'required|string|max:255',
                    'email' => 'required|email|unique:users,email|max:255',
                    'password' => 'required|string|min:8',
                    'telephone_number' => 'required|string|max:20',
                    'street' => 'required|string|max:255',
                    'house_number' => 'required|string|max:10',
                    'unit_number' => 'nullable|string|max:10',
                    'postcode' => 'required|string|max:20',
                    'city' => 'required|string|max:255',
                    'country' => 'required|string|max:255',
                ]
            );

            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validateUser->errors()
                ], 403);
            }


            //EXTREMELY IMPORTANT: DELETE THE ADMINS ARRAY 
            $admins = ['suhelfares110@gmail.com', 'wiebe@example.com', 'brandon@example.com','wiebe.cloet@student.howest.be', 'brandon.quetstroey@student.howest.be']; 
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'telephone_number' => $request->telephone_number,
                'street' => $request->street,
                'house_number' => $request->house_number,
                'unit_number' => $request->unit_number,
                'postcode' => $request->postcode,
                'city' => $request->city,
                'country' => $request->country,
                'is_admin' => in_array($request->email, $admins) ? 1 : 0
            ]);

            $user->sendEmailVerificationNotification();

            return response()->json([
                'status' => true,
                'message' => 'User Created Successfully',
            ], 201);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function loginUser(Request $request)
    {
        try {
            // Validate login credentials
            $validateUser = Validator::make($request->all(),
            [
                'email' => 'required|email',
                'password' => 'required'
            ]);

            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }

            // Attempt to authenticate the user
            if (!Auth::attempt($request->only(['email', 'password']))) {
                throw ValidationException::withMessages([
                    'email' => __('The provided credentials do not match our records.'),
                ]);
            }

            $user = User::where('email', $request->email)->first();
            // Regenerate the session to prevent fixation attacks
            $request->session()->regenerate();

            //Check if email is verified
            if (is_null($user->email_verified_at)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Your email has not been verified. Please verify your email to log in.',
                ], 403);
            }

            return response()->json([
                'status' => true,
                'message' => 'Login successful.',
            ], 200);

        } catch (ValidationException $exception) {
            return response()->json([
                'status' => false,
                'message' => 'Validation exception occurred.',
                'errors' => $exception->errors(),
            ], 422);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred: ' . $th->getMessage(),
            ], 500);
        }
    }

    public function refresh(Request $request)
    {
        // Regenerate the session to refresh the session ID
        $request->session()->regenerate();

        return response()->json([
            'status' => true,
            'message' => 'Session refreshed successfully.',
        ], 200);
    }

    public function logout()
    {
        Auth::guard('web')->logout();

        return response()->json([
            'status' => true,
            'message' => 'Logout successful.',
        ], 200);
    }

    public function checkAuth(Request $request)
    {
        if (Auth::check()) {
            return response()->json([
                'status' => true,
                'message' => 'User is authenticated.',
                'user' => Auth::user(),
            ], 200);
        }

        return response()->json([
            'status' => false,
            'message' => 'User is not authenticated.',
        ], 401);
    }

    public function update(Request $request)
    {
        // Validate the input fields
        $validated = $request->validate([
            'last_name' => 'string|max:255',
            'first_name' => 'string|max:255',
            'telephone_number' => 'string|max:15',
            'street' => 'string|max:255',
            'house_number' => 'string|max:10',
            'unit_number' => 'nullable|string|max:10',
            'postcode' => 'string|max:10',
            'city' => 'string|max:255',
            'country' => 'string|max:255',
        ]);

        // Get the authenticated user from the session using the web guard
        $user = Auth::guard('web')->user();

        if (!$user) {
            return response()->json([
                'message' => 'User not authenticated.'
            ], 401);
        }

        // Update the allowed fields only
        $user->update($validated);

        return response()->json([
            'message' => 'User updated successfully.',
            'user' => $user,
        ]);
    }

    public function checkAdmin(Request $request)
    {
        $user = Auth::guard('web')->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not authenticated.'
            ], 401);
        }

        if ($user->is_admin) {
            return response()->json([
                'status' => true,
                'message' => 'User is an admin.'
            ], 200);
        }

        return response()->json([
            'status' => false,
            'message' => 'User is not an admin.'
        ], 403);
    }

    public function verifyEmail(Request $request, $id, $hash){
        // Find the user by ID
        $user = User::findOrFail($id);

        // Validate the hash
        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            abort(403, 'Invalid verification link.');
        }

        // Check if the email is already verified
        if ($user->hasVerifiedEmail()) {
            return redirect('/email-verified-success?name=' . urlencode($user->name));
        }

        // Mark the email as verified
        $user->markEmailAsVerified();

        // Redirect to success page
        return redirect('/email-verified-success?name=' . urlencode($user->name));
   }

   public function sendVerificationEmail(Request $request){
        // Send the email verification notification
        $request->user()->sendEmailVerificationNotification();

        // Return a JSON response
        return response()->json([
            'message' => 'Verification link sent!'
        ]);
   }

   public function me(Request $request)
   {
       // Check if the user is authenticated
       if (!Auth::guard('web')->check()) {
           return response()->json([
               'error' => 'Unauthorized'
           ], 401);
    }

       // Get the authenticated user
       $user = Auth::guard('web')->user();

       // Return the user
       return response()->json([
           'user' => $user
       ]);
   }
}


