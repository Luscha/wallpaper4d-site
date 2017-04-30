<?php

namespace App;

use App\Http\Controllers\ImageController;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Carbon\Carbon;

/**
 * App\Image
 *
 * @property integer $id
 * @property string $iid
 * @property string $format
 * @property string $quality
 * @property integer $height
 * @property integer $width
 * @property integer $channel_id
 * @property boolean $is_default
 * @property boolean $is_private
 * @property boolean $is_thumbnail
 * @property boolean $age_restricted
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 * @method static \Illuminate\Database\Query\Builder|\App\Image whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Image whereIid($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Image whereFormat($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Image whereQuality($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Image whereHeight($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Image whereWidth($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Image whereChannelId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Image whereIsDefault($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Image whereIsPrivate($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Image whereIsThumbnail($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Image whereAgeRestricted($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Image whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Image whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Image whereDeletedAt($value)
 * @mixin \Eloquent
 */
class Image extends Model
{
    use SoftDeletes;
    
    protected $table = 'images';
    protected $dates = ['deleted_at'];
    protected $fillable = ['format', 'quality', 'width', 'height'];

    public function is_accessible()
    {
        // TODO: Check ownership of private images
        if ($this->is_private) // if ($i->is_private && Request::user() != $i->channel_id)
            return false;

        return true;
    }

    /*protected function tags()
    {
        return $this->belongsToMany('App\Tag', 'image_has_tags');
    }*/

    public function thumbnail($q = 'hq', $w = -1, $h = -1)
    {
        if ($w <= 0 || $h <= 0)
            return sprintf("/vi/%s/%sdefault.jpg?sigh=%s", $this->iid, $q, ImageController::sigh());
        else
            return sprintf("/vi/%s/%sdefault.jpg?custom=true&w=%u&h=%u&sigh=%s", $this->iid, $q, $w, $h, ImageController::sigh());
    }

    public function render()
    {
        return sprintf("/i/%s?sigh=%s", $this->iid, ImageController::sigh());
    }
}
