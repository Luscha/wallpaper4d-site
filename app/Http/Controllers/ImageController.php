<?php

namespace App\Http\Controllers;

use App\Image;
use App\ImageViews;
use Carbon\Carbon;
use Illuminate\Support\Facades\Request;

use App\Http\Requests;

class ImageController extends Controller
{
    private $default_media_folder = 'media';
    private $default_thumbnails_folder = 'thumbnails';
    private $default_errors_folder = 'errors';
    private $default_images_folder = 'images';

    // FORMAT: JPEG, WEBP
    private $allowed_formats = [
        'JPEG' => 'jpg',
        'WEBP' => 'webp'
    ];

    // For more info see: https://developers.google.com/youtube/v3/docs/thumbnails
    private $allowed_resolutions = [
        'default' => [180, 90], // Thumbnail
        'mqdefault' => [320, 180], // Medium Quality Thumbnail
        'hqdefault' => [480, 360], // High Quality Thumbnail
        'sddefault' => [640, 480], // Standard Definition Thumbnail
        'maxresdefault' => [1280, 720], // Maximum Resolution Thumbnail
    ];

    static protected $signature_private_key = "8JGAXI4DQv2sauYwovtTBV8oKxyP8TId";

    private function fallback($code = 404, $is_thumbnail = false, $format = 'JPEG')
    {
        switch ($code)
        {
            case 401: // Unauthorized (18+ content)
                return $this->thumbnail_path('401', true, 'hqdefault', $this->default_errors_folder, $format);
            case 404: // Not Found (not found, not owner)
            default:
                return $this->thumbnail_path('404', true, 'hqdefault', $this->default_errors_folder, $format);
        }
    }

    public function image_path($iid = null, $folder = null, $format = 'JPEG')
    {
        if (is_null($iid) || empty($iid) || !array_key_exists($format, $this->allowed_formats))
            return null;

        if (is_null($folder))
            $folder = $this->default_images_folder;

        $image_path = sprintf('%s/%s/%s.%s',
            $this->default_media_folder, $folder,
            $iid, $this->allowed_formats[$format]
        );
        return storage_path($image_path);
    }

    public function thumbnail_path($iid = null, $is_default = true, $q = 'hqdefault', $folder = null, $format = 'JPEG')
    {
        if (is_null($iid) || empty($iid))
            return null;

        if (!array_key_exists($q, $this->allowed_resolutions) || !array_key_exists($format, $this->allowed_formats))
            return null;

        if ($is_default)
        {
            $w = $this->allowed_resolutions[$q][0];
            $h = $this->allowed_resolutions[$q][1];
        }
        else
        {
            $w = (int)Request::input('w');
            $h = (int)Request::input('h');
        }

        if (is_null($folder))
            $folder = $this->default_images_folder;

        $image_path = sprintf('%s/%s/%s_%s_%ux%u.%s',
            $this->default_media_folder, $folder,
            $iid, $q, $w, $h, $this->allowed_formats[$format]
        );
        return storage_path($image_path);
    }

    private function has_custom_resolution($q)
    {
        return $q == 'hqdefault' && Request::has('custom') && Request::has('w') && Request::has('h');
    }

    // Get unique signature for image request
    static public function sigh($when = null, $ip = null)
    {
        $raw = ImageController::$signature_private_key . (is_null($when) ? Carbon::today() : $when) . (is_null($ip) ? Request::ip() : $ip);
        $base64 = base64_encode(sha1($raw, true));
        $sigh = str_replace(['\\', '/', '='], ['-', '_', ''], $base64);

        return $sigh;
    }

    private function increment_views($iid, $is_thumbnail = false)
    {
        $is_internal = Request::has('sigh');
        if ($is_internal)
        {
            $sigh = Request::input('sigh');
            $is_internal = $this->sigh() == $sigh || $this->sigh(Carbon::yesterday()) == $sigh;
        }

        // Try to update the record by incrementing its value
        $is_valid = ImageViews::where(['image_iid' => $iid, 'is_thumbnail' => $is_thumbnail])
            ->increment($is_internal ? "internal_views" : "external_views");

        // If query failed insert a new record
        if (!$is_valid)
        {
            $record = new ImageViews;
            $record->image_iid = $iid;
            $record->is_thumbnail = $is_thumbnail;
            $record->internal_views = $is_internal ? 1 : 0;
            $record->external_views = $is_internal ? 0 : 1;
            $record->save();
        }
    }

    // ARGS: custom (bool|opt), w (int|opt), h (int|opt), stc (bool|opt),
    //       jpg444 (bool|opt), jpgq (int|opt), sp (int|opt), sigh (string|opt)
    // ORGIN-CHECK: sigh

    // https://i.ytimg.com/vi/pfWpzHUGECw/hqdefault.jpg?custom=true&w=168&h=94&stc=true&jpg444=true&jpgq=90&sp=68&sigh=6PVhDBzfTTvP5H3oifttlwqd5GY
    // https://i.ytimg.com/vi/vX-ePtYzCjo/hqdefault.jpg?custom=true&w=196&h=110&stc=true&jpg444=true&jpgq=90&sp=68&sigh=h4lBG5jy2EZ3NxsKaH38vdATGdI
    public function thumbnail($iid, $quality, $format = 'JPEG')
    {
        $i = Image::where(['iid' => $iid, 'is_thumbnail' => true, 'format' => $format])->first();
        if (is_null($i) || !$i->is_accessible($i))
            return response()->file($this->fallback());

        // TODO: Age restriction check for users
        //if ($i->age_restricted && Request::user()->block_age_restricted)
        //    return response()->file($this->fallback(401));

        $is_default = !$this->has_custom_resolution($quality);

        $resource_path = $this->thumbnail_path($iid, $is_default, $quality, $this->default_thumbnails_folder, $format);
        $is_valid = !is_null($resource_path) && \File::exists($resource_path);

        // TODO: Check in database if resolution is available,
        // TODO: if not fallback to the closer (higher if available) resolution

        // Try to locate the default image with no custom width and height
        if (!$is_valid && !$is_default)
        {
            $resource_path = $this->thumbnail_path($iid, true, $quality, $this->default_thumbnails_folder, $format);
            $is_valid = \File::exists($resource_path);
        }

        // If image have not been found return the default image
        if (!$is_valid)
            return response()->file($this->fallback());

        $this->increment_views($iid, true);
        return response()->file($resource_path);
    }

    public function thumbnail_jpeg($iid, $quality)
    {
        return $this->thumbnail($iid, $quality, 'JPEG');
    }

    public function thumbnail_webp($iid, $quality)
    {
        return $this->thumbnail($iid, $quality, 'WEBP');
    }

    // https://yt3.ggpht.com/-iXsHn-XFN1Y/AAAAAAAAAAI/AAAAAAAAAAA/3uSpNWbE5QE/s100-c-k-no-rj-c0xffffff/photo.jpg
    // https://yt3.ggpht.com/-QpN-qJY73w8/AAAAAAAAAAI/AAAAAAAAAAA/DoR6wkrFs14/s88-c-k-no-mo-rj-c0xffffff/photo.jpg
    // https://yt3.ggpht.com/-9itAE6Cfdfk/AAAAAAAAAAI/AAAAAAAAAAA/x1PlFvSU3Qk/s28-c-k-no-rj-c0xffffff/photo.jpg
    // https://yt3.ggpht.com/-uJoEsv-zYhc/VTB0YeYschI/AAAAAAAAAGc/D_Sp9e_hrMw/w1060-fcrop64=1,00005a57ffffa5a8-nd-c0xffffffff-rj-k-no/instalok-youtube.png
    // https://yt3.ggpht.com/-uJoEsv-zYhc/VTB0YeYschI/AAAAAAAAAGc/D_Sp9e_hrMw/w2120-fcrop64=1,00005a57ffffa5a8-nd-c0xffffffff-rj-k-no/instalok-youtube.png
    // https://yt3.ggpht.com/o8mAqOHxfMkpKCeTb1p0VmhR1cHbpG-2lZTXIfWAJv5vucDNluqVpgo8iF1D3CMRF9dd1WZ1x_0HxnfQhw=w40-nd
    // https://yt3.ggpht.com/Q-bo9nk6u8blLNNSU0JwZgnBPOyCT2pkrBCwrPqTcwTNR6UHMqfuzPxmKIEWdLuH3Oh6WMGydXSskAl2FIs=w40-nd
    public function jpeg($iid, $opt = null)
    {
        $i = Image::where(['iid' => $iid, 'is_thumbnail' => false, 'format' => 'JPEG'])->first();
        if (is_null($i) || !$i->is_accessible($i))
            return response()->file($this->fallback());

        $resource_path = $this->image_path($iid, $this->default_images_folder);
        $is_valid = !is_null($resource_path) && \File::exists($resource_path);

        // If image have not been found return the default image
        if (!$is_valid)
            return response()->file($this->fallback());

        $this->increment_views($iid, true);
        return response()->file($resource_path);
    }

    public function jpeg2($iid, $p1, $p2, $p3, $opt, $name, $ext)
    {
        return $this->jpeg($iid, $opt);
    }
}
