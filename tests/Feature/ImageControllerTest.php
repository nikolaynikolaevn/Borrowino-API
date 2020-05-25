<?php

namespace Tests\Feature;

use App\Http\Controllers\ImageController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageControllerTest extends TestCase
{
    use RefreshDatabase, WithoutMiddleware;

    /**
     * @test
     */
    public function uploadOfferImages()
    {
        // Arrange
        $image1 = 'image1.jpg';
        $image2 = 'image2.jpg';
        $fakeImages = [UploadedFile::fake()->image($image1), UploadedFile::fake()->image($image2)];

        // Act
        (new \App\Http\Controllers\ImageController)->uploadImages($fakeImages, 1, 'offer_image');
        $imagesInDatabase = DB::table('images')->where('path_to_image', 'like', '%' . $image1 . '%')
            ->orWhere('path_to_image', 'like', '%' . $image2 . '%')
            ->get();

        // Assert
        Storage::disk('local')->assertExists($imagesInDatabase[0]->path_to_image);
        Storage::disk('local')->assertExists($imagesInDatabase[1]->path_to_image);
    }

    /**
     * @test
     */
    public function uploadProfileImage()
    {
        // Arrange
        $image1 = 'image1.jpg';
        $fakeImages = [UploadedFile::fake()->image($image1)];

        // Act
        (new \App\Http\Controllers\ImageController)->uploadImages($fakeImages, 1, 'profile_image');
        $imagesInDatabase = DB::table('images')->where('path_to_image', 'like', '%' . $image1 . '%')
            ->get();

        // Assert
        Storage::disk('local')->assertExists($imagesInDatabase[0]->path_to_image);
    }

    /**
     * @test
     */
    public function deleteOfferImages()
    {
        // Arrange
        $image1 = 'image1.jpg';
        $image2 = 'image2.jpg';
        $fakeImages = [UploadedFile::fake()->image($image1), UploadedFile::fake()->image($image2)];

        (new \App\Http\Controllers\ImageController)->uploadImages($fakeImages, 1, 'offer_image');
        $imagesInDatabaseBeforeDelete = DB::table('images')->where('path_to_image', 'like', '%' . $image1 . '%')
            ->orWhere('path_to_image', 'like', '%' . $image2 . '%')
            ->get();

        // Act
        (new \App\Http\Controllers\ImageController)->deleteImages(1, 'offer_image');
        $imagesInDatabaseAfterDelete = DB::table('images')->where('path_to_image', 'like', '%' . $image1 . '%')
            ->orWhere('path_to_image', 'like', '%' . $image2 . '%')
            ->get();

        // Assert
        $this->assertEquals(0, count($imagesInDatabaseAfterDelete));

        $image1Exists= Storage::disk('local')->exists($imagesInDatabaseBeforeDelete[0]->path_to_image);
        $this->assertFalse($image1Exists);
        $image2Exists = Storage::disk('local')->exists($imagesInDatabaseBeforeDelete[1]->path_to_image);
        $this->assertFalse($image2Exists);
    }

    /**
     * @test
     */
    public function deleteProfileImage()
    {
        // Arrange
        $image1 = 'image1.jpg';
        $fakeImages = [UploadedFile::fake()->image($image1)];

        (new \App\Http\Controllers\ImageController)->uploadImages($fakeImages, 1, 'offer_image');
        $imagesInDatabaseBeforeDelete = DB::table('images')->where('path_to_image', 'like', '%' . $image1 . '%')
            ->get();

        // Act
        (new \App\Http\Controllers\ImageController)->deleteImages(1, 'offer_image');
        $imagesInDatabaseAfterDelete = DB::table('images')->where('path_to_image', 'like', '%' . $image1 . '%')
            ->get();

        // Assert
        $this->assertEquals(0, count($imagesInDatabaseAfterDelete));

        $image1Exists= Storage::disk('local')->exists($imagesInDatabaseBeforeDelete[0]->path_to_image);
        $this->assertFalse($image1Exists);
    }
}
