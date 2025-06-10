<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
//use Spatie\Permission\Models\Role;

class User extends Authenticatable implements FilamentUser, HasName
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use Notifiable;
    use HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'emp_name',
        'last_name',
        'emp_id',
        'dept_id',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

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

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasRole(['Admin','Safety','User']);
    }
        public function getAuthIdentifierName()
    {
        return 'emp_id';
    }
    public function getFilamentName(): string
    {
        return "{$this->emp_id} {$this->emp_name} {$this->last_name}";
    }

    public function getFullNameAttribute(): string
    {
        return trim(($this->emp_id ?? '').' ('.($this->emp_name ?? '') . ' ' . ($this->last_name ?? '').')');
    }

    public function deptID() {
        return $this->belongsTo(Department::class,'dept_id','dept_id');
    }

}
