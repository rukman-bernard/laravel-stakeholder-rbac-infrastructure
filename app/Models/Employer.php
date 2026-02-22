<?php

namespace App\Models;

use App\Notifications\EmployerResetPasswordNotification;
use App\Traits\AdminLteUserInterface;
use Illuminate\Auth\Passwords\CanResetPassword as CanResetPasswordTrait;
use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class Employer extends Authenticatable implements CanResetPassword, MustVerifyEmail
{
    use HasFactory;
    use Notifiable;
    use AdminLteUserInterface;
    use CanResetPasswordTrait;
    use MustVerifyEmailTrait;

    /**
     * Mass assignable attributes.
     *
     * NOTE: include image_path if you support profile photos for employers.
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'image_path',
    ];

    /**
     * Hide sensitive fields from serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Casts.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Polymorphic address relationship.
     */
    public function address()
    {
        return $this->morphOne(Address::class, 'addressable');
    }

    /**
     * Computed profile image URL for AdminLTE + profile views.
     *
     * Uses public disk + /storage symlink.
     */
    public function getProfileImageUrlAttribute(): string
    {
        if ($this->image_path && Storage::disk('public')->exists($this->image_path)) {
            return asset('storage/' . $this->image_path);
        }

        return asset('images/default-avatar.png');
    }

    /**
     * Send password reset using the employer notification.
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new EmployerResetPasswordNotification($token));
    }

    /**
     * Used by your guard-aware UI/helpers.
     */
    public function authGuardName(): string
    {
        return 'employer';
    }
}