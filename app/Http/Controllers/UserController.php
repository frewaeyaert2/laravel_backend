<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Mail;
use Validator;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Log::info($request->all());
        $perPage = $request->query('per_page');
        $page = $request->query('page');

        // pagination

        try {
            $validatedData = $this->validateQueryParams($request);

            $users = User::query();

            foreach ($validatedData as $key => $value) {
                // they dont have to match exactly, they can be like
                // ex. name = Susan, when su is given Susan should match
                $users->where($key, 'like', '%' . $value . '%');
            }

            return $users->paginate($perPage, ['*'], 'page', $page);
        } catch (ValidationException $e) {
            Log::error('Validation error: ', $e->errors());
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    private function validateQueryParams(Request $request)
    {
        return $request->validate([
            'first_name' => 'string|max:255',
            'last_name' => 'string|max:255',
            'email' => 'email',
            'email_verified_at' => 'boolean',
            'is_admin' => 'boolean',
            //TODO: fix this'created_at' => 'date',
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $user = User::find($id);

        if ($user) {
            return response()->json([
                'status' => true,
                'message' => 'User found.',
                'user' => $user,
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'User not found.',
            ], 404);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        $id = $request->route('id');
        $user = User::find($id);
        Log::info("deleting a user" . $user);
        $user->delete();
    }

    public function sendForgotPasswordEmail(Request $request)
    {
        // Validate the email
        $validation = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validation->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Error',
                'errors' => $validation->errors(),
            ], 422);
        }

        try {
            $user = User::where('email', $request->input('email'))->first();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found.',
                ], 404);
            }

            $resetUrl = url('http://q-pigeon.test/forgot-password?email=' . urlencode($user->email));


            Log::info('Reset URL: ' . $resetUrl);


            Mail::send('emails.reset-password', [
                'user' => $user, // Pass the $user variable
                'resetUrl' => $resetUrl // Pass the $resetUrl variable
            ], function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('Reset Your Password');
            });


            return response()->json([
                'status' => true,
                'message' => 'A password reset email has been sent.',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while sending the reset email.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function updatePassword(Request $request)
    {

        Log::info($request->all());
        // Validate the request
        $validation = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string|min:8|confirmed', // Requires 'password_confirmation'
        ]);

        if ($validation->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Error',
                'errors' => $validation->errors(),
            ], 422);
        }

        try {
            // Find the user by email
            $user = User::where('email', $request->input('email'))->first();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found.',
                ], 404);
            }

            // Update the user's password
            $user->update([
                'password' => Hash::make($request->input('password')),
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Your password has been successfully updated.',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while resetting the password.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
