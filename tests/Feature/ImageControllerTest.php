<?php

namespace Tests\Feature;

use App\Http\Controllers\ImageController;
use App\Offer;
use App\User;
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

        $image1Exists = Storage::disk('local')->exists($imagesInDatabaseBeforeDelete[0]->path_to_image);
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

        $image1Exists = Storage::disk('local')->exists($imagesInDatabaseBeforeDelete[0]->path_to_image);
        $this->assertFalse($image1Exists);
    }

    /**
     * @test
     */
    public function fetchImage_returnOfferImages()
    {
        // Arrange
        $image1 = 'image1.jpg';
        $image2 = 'image2.jpg';
        $fakeImages = [UploadedFile::fake()->image($image1), UploadedFile::fake()->image($image2)];
        factory(Offer::class)->create([
            'images' => true,
        ]);

        (new \App\Http\Controllers\ImageController)->uploadImages($fakeImages, 1, 'offer_image');

        // Act
        $response = $this->postJson(route('images.fetch'), [
            'resource_id' => 1,
            'resource_type' => 'offer_image',
        ]);

        // Assert
        $this->assertThat($response->headers->get('content-type'), $this->equalTo('application/zip'), "Image likely not found");
    }

    /**
     * @test
     */
    public function fetchImage_returnProfileImage()
    {
        // Arrange
        $image1 = 'image1.jpg';
        $fakeImages = [UploadedFile::fake()->image($image1)];
        (new \App\Http\Controllers\ImageController)->uploadImages($fakeImages, 1, 'profile_image');
        factory(User::class)->create();

        // Act
        $response = $this->postJson(route('images.fetch'), [
            'resource_id' => 1,
            'resource_type' => 'profile_image',
        ]);

        // Assert
        $response->assertStatus(200);
        $this->assertThat($response->headers->get('content-type'), $this->equalTo('application/zip'), "Image likely not found");
    }

    /**
     * @test
     */
    public function fetchImage_errorWhenResourceIdIsNotANumberMissingOrLessThanZero()
    {
        // Arrange
        /*This is only so PHPUnit does not get a fatal error*/
        $image1 = 'image1.jpg';
        $fakeImages = [UploadedFile::fake()->image($image1)];
        (new \App\Http\Controllers\ImageController)->uploadImages($fakeImages, 1, 'offer_image');

        // Act
        $response = $this->postJson(route('images.fetch'), [
            'resource_id' => 'String',
            'resource_type' => 'profile_image',
        ]);

        // Assert
        $this->assertThat($response->getStatusCode(), $this->equalTo(422), 'resource_id was a string but should not be allowed');

        // Act
        $response = $this->postJson(route('images.fetch'), [
            'resource_type' => 'profile_image',
        ]);

        // Assert
        $this->assertThat($response->getStatusCode(), $this->equalTo(422), 'resource_id was missing but should not be allowed');

        // Act
        $response = $this->postJson(route('images.fetch'), [
            'resource_id' => 0,
            'resource_type' => 'profile_image',
        ]);

        // Assert
        $this->assertThat($response->getStatusCode(), $this->equalTo(422), 'resource_id was less than 1 but should not be allowed');
    }

    /**
     * @test
     */
    public function fetchImages_errorWhenResourceTypeIsMissingOrNotAPredefinedString()
    {
        // Arrange
        /*This is only so PHPUnit does not get a fatal error*/
        $image1 = 'image1.jpg';
        $fakeImages = [UploadedFile::fake()->image($image1)];
        (new \App\Http\Controllers\ImageController)->uploadImages($fakeImages, 1, 'offer_image');

        // Act
        $response = $this->postJson(route('images.fetch'), [
            'resource_id' => 1,
        ]);

        // Assert
        $this->assertThat($response->getStatusCode(), $this->equalTo(422), 'resource_type is missing but should not be allowed');

        // Act
        $response = $this->postJson(route('images.fetch'), [
            'resource_id' => 1,
            'resource_type' => 0,
        ]);

        // Assert
        $this->assertThat($response->getStatusCode(), $this->equalTo(422), 'resource_type is an integer but only string should be allowed');

        // Act
        $response = $this->postJson(route('images.fetch'), [
            'resource_id' => 1,
            'resource_type' => 'notAllowed'
        ]);

        // Assert
        $this->assertThat($response->getStatusCode(), $this->equalTo(422), 'resource_type is not an allowed string but is accepted');
    }

    /**
     * @test
     */
    public function fetchImages_notFoundStatusWhenResourceNotFound()
    {
        // Arrange
        /*This is only so PHPUnit does not get a fatal error*/
        $image1 = 'image1.jpg';
        $fakeImages = [UploadedFile::fake()->image($image1)];
        (new \App\Http\Controllers\ImageController)->uploadImages($fakeImages, 1, 'offer_image');

        // Act
        $response = $this->postJson(route('images.fetch'), [
            'resource_id' => 1,
            'resource_type' => 'offer_image',
        ]);

        // Assert
        $response->assertNotFound();
    }

    /**
     * @test
     */
    public function fetchImages_notFoundWhenOfferHasNoImages()
    {
        // Arrange
        /*This is only so PHPUnit does not get a fatal error*/
        $image1 = 'image1.jpg';
        $fakeImages = [UploadedFile::fake()->image($image1)];
        (new \App\Http\Controllers\ImageController)->uploadImages($fakeImages, 1, 'profile_image');

        factory(Offer::class)->create();

        // Act
        $response = $this->postJson(route('images.fetch'), [
            'resource_id' => 1,
            'resource_type' => 'offer_image',
        ]);

        // Assert
        $this->assertNotEquals('application/zip', $response->headers->get('content-type'), 'Zip was returned when it should not have been');
        $this->assertThat($response->getStatusCode(), $this->equalTo(404));
    }

    /**
     * @test
     */
    public function fetchImages_notFoundWhenUserHasNoImages()
    {
        // Arrange
        /*This is only so PHPUnit does not get a fatal error*/
        $image1 = 'image1.jpg';
        $fakeImages = [UploadedFile::fake()->image($image1)];
        (new \App\Http\Controllers\ImageController)->uploadImages($fakeImages, 1, 'offer_image');

        factory(User::class)->create();

        // Act
        $response = $this->postJson(route('images.fetch'), [
            'resource_id' => 1,
            'resource_type' => 'profile_image',
        ]);

        // Assert
        $this->assertNotEquals('application/zip', $response->headers->get('content-type'), 'Zip was returned when it should not have been');
        $this->assertThat($response->getStatusCode(), $this->equalTo(404));
    }


}
