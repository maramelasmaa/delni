<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\UpdateAccountRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\AccountSecurityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends BaseApiController
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->validated('name'),
            'email' => mb_strtolower($request->validated('email')),
            'password' => Hash::make($request->validated('password')),
            'is_active' => true,
        ]);

        $user->assignRole('user');

        $token = $user->createToken($request->input('device_name', 'mobile'))->plainTextToken;

        return $this->success([
            'token' => $token,
            'user' => new UserResource($user),
        ], 'تم تسجيل الحساب بنجاح.', 201);
    }

    public function login(LoginRequest $request, AccountSecurityService $accountSecurity): JsonResponse
    {
        $email = mb_strtolower($request->validated('email'));
        $user = User::where('email', $email)->first();

        if (! $user || ! Hash::check($request->validated('password'), $user->password)) {
            $accountSecurity->recordFailedAttempt($email);

            throw ValidationException::withMessages([
                'email' => ['البريد الإلكتروني أو كلمة المرور غير صحيحة.'],
            ]);
        }

        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['تم إلغاء تنشيط هذا الحساب.'],
            ]);
        }

        if ($user->is_suspended) {
            throw ValidationException::withMessages([
                'email' => ['تم تعليق هذا الحساب.'],
            ]);
        }

        if ($user->locked_until !== null && now()->lt($user->locked_until)) {
            throw ValidationException::withMessages([
                'email' => ['الحساب مقفل مؤقتاً بسبب محاولات تسجيل دخول خاطئة.'],
            ]);
        }

        $accountSecurity->recordSuccessfulLogin($user);

        $token = $user->createToken($request->input('device_name', 'mobile'))->plainTextToken;

        return $this->success([
            'token' => $token,
            'user' => new UserResource($user),
        ], 'تم تسجيل الدخول بنجاح.');
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->success([], 'تم تسجيل الخروج بنجاح.');
    }

    public function me(Request $request): JsonResponse
    {
        return $this->success(new UserResource($request->user()));
    }

    public function updateProfile(UpdateAccountRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();

        if (array_key_exists('email', $data)) {
            $data['email'] = mb_strtolower($data['email']);
        }

        $user->fill($data)->save();

        return $this->success(new UserResource($user), 'تم تحديث المعلومات بنجاح.');
    }

    public function deleteAccount(Request $request): JsonResponse
    {
        $user = $request->user();

        // Anonymize the unique email/phone before soft-deleting so the original values
        // are freed at the database level — otherwise the unique index on `email` keeps
        // blocking re-registration even though validation ignores soft-deleted rows.
        DB::transaction(function () use ($user): void {
            $user->tokens()->delete();
            $user->forceFill([
                'email' => 'deleted+'.$user->id.'+'.Str::random(20).'@deleted.invalid',
                'phone' => null,
            ])->save();
            $user->delete();
        });

        return $this->success([], 'تم حذف الحساب بنجاح.');
    }

    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $user = $request->user();

        if (! Hash::check($request->validated('current_password'), $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['كلمة المرور الحالية غير صحيحة.'],
            ]);
        }

        $currentToken = $user->currentAccessToken();
        $currentTokenId = $currentToken instanceof PersonalAccessToken ? $currentToken->getKey() : null;

        $user->forceFill([
            'password' => Hash::make($request->validated('password')),
            'password_changed_at' => now(),
        ])->save();

        // Revoke every other session; keep the current device signed in.
        $user->tokens()
            ->when($currentTokenId, fn ($query) => $query->where('id', '!=', $currentTokenId))
            ->delete();

        return $this->success([], 'تم تغيير كلمة المرور بنجاح.');
    }

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        Password::sendResetLink(['email' => mb_strtolower($request->validated('email'))]);

        return $this->success([], 'إذا كان البريد الإلكتروني مسجلاً، فستتلقى رابطاً لإعادة تعيين كلمة المرور قريباً.');
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $status = Password::reset(
            array_merge($request->validated(), ['email' => mb_strtolower($request->validated('email'))]),
            function (User $user, string $password): void {
                $user->forceFill(['password' => Hash::make($password)])->save();
                $user->tokens()->delete();
            }
        );

        if ($status !== Password::PasswordReset) {
            throw ValidationException::withMessages([
                'email' => ['الرمز أو البريد الإلكتروني المدخل غير صالح لإعادة تعيين كلمة المرور.'],
            ]);
        }

        return $this->success([], 'تم إعادة تعيين كلمة المرور بنجاح.');
    }
}
