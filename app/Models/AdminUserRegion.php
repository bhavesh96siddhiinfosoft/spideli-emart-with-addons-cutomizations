<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminUserRegion extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'admin_user_regions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'region_id',
    ];

    /**
     * The id of the super admin, who is never bound to a region.
     *
     * @var int
     */
    const SUPER_ADMIN_ID = 1;

    /**
     * Returns the region ids assigned to the given admin user.
     *
     * @param  int  $userId
     * @return array
     */
    public static function regionsOfUser($userId)
    {
        return self::where('user_id', $userId)->pluck('region_id')->toArray();
    }

    /**
     * Replaces the region assignments of the given admin user.
     *
     * @param  int  $userId
     * @param  array  $regionIds
     * @return void
     */
    public static function syncUserRegions($userId, $regionIds)
    {
        self::where('user_id', $userId)->delete();

        if (empty($regionIds)) {
            return;
        }

        foreach (array_unique($regionIds) as $regionId) {
            if ($regionId == '') {
                continue;
            }

            self::create([
                'user_id' => $userId,
                'region_id' => $regionId,
            ]);
        }
    }

    /**
     * The super admin is never bound to a region and therefore sees everything.
     *
     * @param  int  $userId
     * @return bool
     */
    public static function isSuperAdmin($userId)
    {
        return $userId == self::SUPER_ADMIN_ID;
    }
}
