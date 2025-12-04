<?php
/**
 * Class User
 *
 * Represents a user in the system.
 *
 * @property int $id
 * @property string $name Name of the user
 * @property string $email Email address of the user
 * @property string|null $email_verified_at Date and time when the email was verified
 * @property string $password Password hash
 * @property string|null $remember_token Token for remembering the user
 * @property Carbon|null $created_at Date and time when the user was created
 * @property Carbon|null $updated_at Date and time when the user was last updated
 *
 * @method static \Illuminate\Database\Eloquent\Builder|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmail($value)
 */

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\InventoryItem;
use App\Models\Pond;
use App\Models\PondSlotStatus;
use App\Models\ToolUsage;
use App\Models\UserInventory;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    public const INITIAL_WALLET_BALANCE = 500;

    protected static function booted(): void
    {
        static::created(function (User $user): void {
            $user->ensureWalletExists();
            $user->ensureDefaultPond();
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'farm_name',
        'farm_type',
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
            'last_played_at' => 'datetime',
            'days_played' => 'integer',
            'tutorial_completed' => 'boolean',
        ];
    }

    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class);
    }

    public function ponds(): HasMany
    {
        return $this->hasMany(Pond::class);
    }

    public function inventory(): HasMany
    {
        return $this->hasMany(UserInventory::class);
    }

    public function toolUsages(): HasMany
    {
        return $this->hasMany(ToolUsage::class);
    }

    public function inventoryItems(): BelongsToMany
    {
        return $this->belongsToMany(InventoryItem::class, 'user_inventory')
              ->withPivot(['quantity', 'is_favorite'])
              ->withTimestamps();
    }

    public function ensureWalletExists(): Wallet
    {
        return $this->wallet()->firstOrCreate([], [
            'balance' => self::INITIAL_WALLET_BALANCE,
        ]);
    }

    public function ensureDefaultPond(int $slotsToEnsure = 24): ?Pond
    {
        $emptyStatusId = PondSlotStatus::query()
            ->where('name', 'empty')
            ->value('id');

        if (! $emptyStatusId) {
            return null;
        }

        /** @var \App\Models\Pond|null $pond */
        $pond = $this->ponds()->orderBy('id')->first();

        if (! $pond) {
            $pond = $this->ponds()->create([
                'name' => $this->defaultPondName(),
                'status' => 'active',
                'current_day' => 1,
            ]);
        }

        $currentCount = $pond->slots()->count();
        $missing = $slotsToEnsure - $currentCount;

        if ($missing > 0) {
            $slots = [];

            for ($i = 0; $i < $missing; $i++) {
                $slots[] = [
                    'status_id' => $emptyStatusId,
                    'health' => 100,
                    'oxygen_level' => 100,
                    'ph_level' => 100,
                    'feeding_count' => 0,
                    'feeding_limit' => 3,
                ];
            }

            $pond->slots()->createMany($slots);
        }

        return $pond->load(['slots.fish', 'slots.status']);
    }

    protected function defaultPondName(): string
    {
        return $this->farm_name
            ? sprintf('%s Pond', $this->farm_name)
            : 'Main Pond';
    }

    public function recordDailySession(?Carbon $reference = null): void
    {
        $reference = $reference ? $reference->copy() : Carbon::now();

        $this->refresh();

        $lastPlayed = $this->last_played_at ? $this->last_played_at->copy() : null;
        $shouldIncrement = ! $lastPlayed || ! $lastPlayed->isSameDay($reference);

        $attributes = [
            'last_played_at' => $reference,
        ];

        if ($shouldIncrement) {
            $attributes['days_played'] = max(0, (int) $this->days_played) + 1;
        }

        $this->forceFill($attributes)->save();
    }
}
