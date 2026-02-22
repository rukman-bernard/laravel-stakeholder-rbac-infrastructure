<?php

namespace App\Models;

use App\Notifications\StudentResetPasswordNotification;
use App\Traits\AdminLteUserInterface;
use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Auth\Passwords\CanResetPassword as CanResetPasswordTrait;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class Student extends Authenticatable implements CanResetPassword, MustVerifyEmail
{
    use HasFactory;
    use Notifiable;
    use AdminLteUserInterface;
    use CanResetPasswordTrait;
    use MustVerifyEmailTrait;

    /*
    |--------------------------------------------------------------------------
    | Mass Assignment
    |--------------------------------------------------------------------------
    */

    protected $fillable = [
        'name',
        'email',
        'phone',
        'dob',
        'password',
        'image_path',
    ];

    /*
    |--------------------------------------------------------------------------
    | Hidden Attributes
    |--------------------------------------------------------------------------
    */

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /*
    |--------------------------------------------------------------------------
    | Attribute Casting
    |--------------------------------------------------------------------------
    */

    protected function casts(): array
    {
        return [
            'dob' => 'date',
            'email_verified_at' => 'datetime',
            'password' => 'hashed', // ✅ auto-hash on set()
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    /**
     * Canonical profile image URL for UI usage.
     *
     * Uses the public disk URL (expects Laravel storage symlink: public/storage).
     */
    public function getProfileImageUrlAttribute(): string
    {
        $fallback = asset('images/default-avatar.png');

        if (! $this->image_path) {
            return $fallback;
        }

        return Storage::disk('public')->exists($this->image_path)
            ? Storage::disk('public')->url($this->image_path)
            : $fallback;
    }

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    */

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new StudentResetPasswordNotification($token));
    }

    /*
    |--------------------------------------------------------------------------
    | Guard
    |--------------------------------------------------------------------------
    */

    public function authGuardName(): string
    {
        return 'student';
    }
}