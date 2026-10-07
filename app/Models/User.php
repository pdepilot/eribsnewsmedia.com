<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    public function authorProfile(): HasOne
    {
        return $this->hasOne(Author::class);
    }

    public function hasRole(string ...$slugs): bool
    {
        $this->loadMissing('roles');

        return $this->roles->contains(fn (Role $role) => in_array($role->slug, $slugs, true));
    }

    public function hasPermission(string $ability): bool
    {
        $this->loadMissing('roles.permissions');

        if ($this->roles->contains('slug', 'admin')) {
            return true;
        }

        return $this->roles
            ->flatMap(fn (Role $role) => $role->permissions)
            ->contains('slug', $ability);
    }

    public function authorOrCreate(): Author
    {
        if ($this->authorProfile) {
            return $this->authorProfile;
        }

        return $this->authorProfile()->create([
            'name' => $this->name,
            'slug' => Author::uniqueSlug($this->name),
            'email' => $this->email,
        ]);
    }

    public function roleLabel(): string
    {
        $this->loadMissing('roles');

        return $this->roles->pluck('name')->join(', ') ?: 'No role';
    }
}
