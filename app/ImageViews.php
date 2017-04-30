<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\ImageViews
 *
 * @property integer $id
 * @property string $image_iid
 * @property integer $internal_views
 * @property integer $external_views
 * @property boolean $is_thumbnail
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 * @method static \Illuminate\Database\Query\Builder|\App\ImageViews whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\ImageViews whereImageIid($value)
 * @method static \Illuminate\Database\Query\Builder|\App\ImageViews whereInternalViews($value)
 * @method static \Illuminate\Database\Query\Builder|\App\ImageViews whereExternalViews($value)
 * @method static \Illuminate\Database\Query\Builder|\App\ImageViews whereIsThumbnail($value)
 * @method static \Illuminate\Database\Query\Builder|\App\ImageViews whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\ImageViews whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\ImageViews whereDeletedAt($value)
 * @mixin \Eloquent
 */
class ImageViews extends Model
{
    use SoftDeletes;

    protected $table = 'image_views';
    protected $dates = ['deleted_at'];
    protected $fillable = ['external_views', 'internal_views'];
}
