<?php

namespace App\Models;

use App\Enum\RoleEnum;
use App\Notifications\ResetPassword;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passkeys\Contracts\PasskeyUser;
use Laravel\Passkeys\PasskeyAuthenticatable;

class User extends Authenticatable implements FilamentUser, PasskeyUser
{
    use HasFactory, Notifiable, PasskeyAuthenticatable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            $model->password = bcrypt('heslo123');
        });
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email'];
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => RoleEnum::class,
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPassword(route('filament.admin.auth.password-reset.reset')));
    }
}
