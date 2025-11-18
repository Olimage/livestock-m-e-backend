<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\RefreshToken;
use App\Models\User;
use App\Service\AuthService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;

class LoginController extends Controller
{
/// for web auth

public function login(Request $request)
{
    $credentials = $request->validate([
        'email'    => 'required|email',
        'password' => 'required',
    ]);

    if (Auth::guard('web')->attempt([
        'email' => $credentials['email'],
        'password' => $credentials['password'],
    ], $request->boolean('remember'))) {

        $request->session()->regenerate();

        return redirect()
            ->intended(route('home'))
            ->with('success', 'Welcome back, ' . auth()->user()->full_name . '!');
    }

    return back()->withErrors([
        'message' => 'Invalid login credentials',
    ]);
}

    /**
     * Handle logout request
     */
    public function logout(Request $request)
    {
        $name = Auth::user()->name;

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('app.login')
            ->with('success', 'You have been logged out successfully.');
    }

    /// for api auth

    public function signIn(Request $request)
    {

        try {

            $credentials = $request->validate([
                'email'    => 'required|email',
                'password' => 'required',
            ]);

            // Debug: Check if user exists
            $user = User::where('email', $credentials['email'])->first();
            
            if (!$user) {
                return response()->json([
                    'status'  => false,
                    'message' => "User not found with the provided email",
                    'error'   => 'Invalid credentials',
                ], 401);
            }



            $token = auth('api')->attempt($credentials);

            if (! $token) {

                return response()->json([
                    'status'  => false,
                    'message' => "Invalid credentials - password mismatch",
                    'error'   => 'Authentication failed',
                ], 401);
            }

            $user = auth('api')->user();

            // Generate refresh token
            $refreshToken = self::generateRefreshToken($user->id);

            return response()->json([
                'status'        => true,
                'message'       => 'Login successful',
                'token'         => $token,
                'refresh_token' => $refreshToken,
                'token_type'    => 'bearer',
                'expires_in'    => JWTAuth::factory()->getTTL() * 60,
                'user'          => [
                    'id' => $user->id,
                    'full_name' => $user->full_name,
                    'email' => $user->email,
                    'role' => $user->role,
                ],
            ], 200);

        } catch (\Throwable $e) {

            return response()->json([
                'error'  => $e->getMessage(),
                'trace'  => $e->getTrace(),
                'status' => false,
            ], 500);
        }
    }

    public static function generateRefreshToken($userId)
    {
        $token = Str::random(60);

        $expiresAt = Carbon::now()->addMonths(3);

        $refresh_token = RefreshToken::where('user_id', $userId)->first();

        if (! empty($refresh_token)) {

            $refresh_token_details = $refresh_token->update([
                'expires_at' => $expiresAt,
            ]);

        } else {

            $refresh_token_details = RefreshToken::Create(
                ['user_id'   => $userId,
                    'token'      => $token,
                    'expires_at' => $expiresAt,
                ]
            );

            $refresh_token = RefreshToken::where('token', $token)->first();

        }

        return $refresh_token->token;
    }

    public static function validateRefreshToken($token)
    {
        $refreshToken = RefreshToken::where('token', $token)->first();

        if ($refreshToken && $refreshToken->expires_at->isFuture()) {
            return $refreshToken->user_id;
        }

        return null;
    }

    public function signOut()
    {
        try {
            if (! auth('api')->check()) {
                return response()->json([
                    'status'  => false,
                    'message' => 'User not authenticated',
                ], 401);
            }

            JWTAuth::invalidate(JWTAuth::getToken());

            auth('api')->logout();

            return response()->json([
                'status'  => true,
                'message' => 'Logged out successfully',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Logout failed: ' . $e->getMessage(),
            ], 500);
        }
    }

}
