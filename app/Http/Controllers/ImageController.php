<?php

namespace App\Http\Controllers;

use App\Image;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    private $offerImage;
    private $profileImage;
    private $acceptedTypes;

    public function __construct()
    {
        $this->offerImage = 'offer_image';
        $this->profileImage = 'profile_image';
        $this->acceptedTypes = [$this->offerImage, $this->profileImage];
    }

    private function notAllowed(string $resourceType)
    {
        if (!in_array($resourceType, $this->acceptedTypes)) {
            printf("\033[91m ERROR: Resource type not allowed");
            return true;
        }
        return false;
    }

    /**
     * Create a reference in the database using the resourceId and resource type. Then, save the image(s)
     *
     * @param array $images
     * @param int $resourceId
     * @param string $resourceType
     */
    public function uploadImages(array $images, int $resourceId, string $resourceType)
    {
        if ((new ImageController)->notAllowed($resourceType)) {
            return;
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

    /**
     * Delete all images from database and disk with resourceId and resourceType
     *
     * @param int $resourceId
     * @param string $resourceType
     */
    public function deleteImages(int $resourceId, string $resourceType)
    {
        if ((new ImageController)->notAllowed($resourceType)) {
            return;
        }

        $imagesInDatabase = Image::where('type', $resourceType)
            ->where('resource_id', $resourceId)
            ->get();


        foreach ($imagesInDatabase as $image) {
            Storage::disk('local')->delete($image['path_to_image']);
        }
        $imagesInDatabase = $imagesInDatabase->pluck('id');
        Image::destroy($imagesInDatabase);
    }
}
