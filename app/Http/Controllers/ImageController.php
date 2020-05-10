<?php

namespace App\Http\Controllers;

use App\Image;

class ImageController extends Controller
{
    /**
     * Create a reference in the database using the resourceId and resource type. Then, save the image(s)
     *
     * @param array $images
     * @param int $resourceId
     * @param string $resourceType
     */
    public static function uploadImages(array $images, int $resourceId, string $resourceType)
    {
        if (!in_array($resourceType, ['offer_image', 'profile_image'])) {
            printf("\033[91m ERROR: Resource type not allowed");
        }

        foreach ($images as $image) {
            $name = microtime() . '.' . pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME) . '.' . $image->getClientOriginalExtension();
            $path = 'images/';

            $imageDatabaseReference = new Image();
            $imageDatabaseReference->resource_id = $resourceId;
            $imageDatabaseReference->path_to_image = $path . $name;
            $imageDatabaseReference->type = $resourceType;
            $imageDatabaseReference->save();

            $image->storeAs($path, $name);
        }
    }
}
