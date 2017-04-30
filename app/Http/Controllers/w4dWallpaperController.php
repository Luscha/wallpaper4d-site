<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Request;

use App\Image;

class w4dWallpaperController
{
    public function GetWallpaper()
    {
        $input = Request::All();
        $image = $this->GetImageByParameter($input);

        //Use new method to generate thumbnail?
        $response["url"] = $image->render();

        return response()->json($response);
    }

    private function GetImageByParameter($params)
    {
        $query = Image::select();
        foreach($params as $name => $value)
        {
            // Maybee new function or map to do it better
            if ($name == "orientation" && $value == "vertical")
                $query->where("height", '>', "width");
            elseif ($name == "orientation" && $value == "vertical")
                $query->where("height", '<', "width");
        }

        return $query->inRandomOrder()->first();
    }
}