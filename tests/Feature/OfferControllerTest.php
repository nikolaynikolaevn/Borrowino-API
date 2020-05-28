<?php

namespace Tests\Http\Controllers;

use App\Offer;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OfferControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Get all files in a directory
        $files = Storage::disk('local')->allFiles('images');

        // Delete Files
        Storage::delete($files);
    }

    use RefreshDatabase, WithoutMiddleware;


    /**
     * @test
     */
    public function index_returnAllOffers()
    {
        // Arrange
        $offersExpected = factory(Offer::class, 2)->create();

        // Act

        // Assert
        $this->getJson(route('offers.index'))
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['*' => ['id', 'created_at', 'updated_at', 'title', 'description', 'location', 'price', 'owner']]
            ])
            ->assertJson(['data' => $offersExpected->toArray()]);
    }

    /**
     * @test
     */
    public function index_paginationWorks()
    {
        // Arrange
        $offersExpected = factory(Offer::class, 16)->create();
        $queryParameters = 'page=2';

        // Act
        //        $response = $this->get('/api/offers' . '?' . $queryParameters); // this is a custom function. you can use $this->get(...)
        $response = $this->get(route('offers.index') . '?' . $queryParameters); // this is a custom function. you can use $this->get(...)

        // Assert
        $this->assertEquals('200', $response->getStatusCode());

        // convert JSON response string to Array
        $responseArray = json_decode($response->getContent());

        // assert the second page returned the "x" additional data
        $this->assertCount(1, $responseArray->data);
    }

    //  For some reason this test does not work, the method works as expected in Postman

    /**
     * @test
     */
    public function show_showRequestedOffer()
    {
        // Arrange
        $offerExpected = factory(Offer::class)->create();

        // Act
        $response = $this->getJson(route('offers.show', 1));

        // Assert
        $response->assertStatus(200)
            ->assertJson($offerExpected->toArray());
    }

    /**
     * @test
     */
    public function store_offerWithNoImagesStoredNoImagesInDatabaseAndDisk()
    {
        // Arrange
        $response = null;
        $TITLE = 'title';
        $DESCRIPTION = 'description';
        $LOCATION = 'location';
        $PRICE = 30;

        $user = factory(User::class)->create();

        // Act
        $response = $this->actingAs($user)->postJson(route('offers.store'), [
            'title' => $TITLE,
            'description' => $DESCRIPTION,
            'location' => $LOCATION,
            'price' => $PRICE,
        ]);
        $imagesInDatabaseAfterDelete = DB::table('images')->get();

        // Assert
        $response->assertCreated();
        $this->assertEquals(0, count($imagesInDatabaseAfterDelete));
        $this->assertEmpty(Storage::disk('local')->files('images/'));
    }


    /**
     * @test
     */
    public function store_offerIsStoredWithImages()
    {
        // Arrange
        $response = null;
        $TITLE = 'title';
        $DESCRIPTION = 'description';
        $LOCATION = 'location';
        $PRICE = 30;

        Storage::fake('images');

        $user = factory(User::class)->create();
        $image1 = 'image1.jpg';
        $image2 = 'image2.jpg';

        // Act
        $response = $this->actingAs($user)->postJson(route('offers.store'), [
            'title' => $TITLE,
            'description' => $DESCRIPTION,
            'location' => $LOCATION,
            'price' => $PRICE,
            'images' => [UploadedFile::fake()->image($image1), UploadedFile::fake()->image($image2)],
        ]);

        $imagesInDatabase = DB::table('images')->where('path_to_image', 'like', '%' . $image1 . '%')
            ->orWhere('path_to_image', 'like', '%' . $image2 . '%')
            ->get();

        // Assert

        $response->assertCreated();
        $this->assertDatabaseHas('offers', [
            'title' => $TITLE,
            'description' => $DESCRIPTION,
            'location' => $LOCATION,
            'price' => $PRICE,
            'images' => true,
        ]);

        $this->assertEquals(2, count($imagesInDatabase));

        $this->assertFileExists(public_path($imagesInDatabase[0]->path_to_image));
        $this->assertFileExists(public_path($imagesInDatabase[1]->path_to_image));

        return $response;
    }

    /**
     * @test
     */
    public function destroy_offerIsDestroyedWhenOwner()
    {
        // Arrange
        $offer = factory(Offer::class)->create([
            // We do this because the timestamps are not equal when they are returned from the database.
            // This is normal behavior
            'created_at' => null,
            'updated_at' => null,
        ]);
        $user = User::find(1);


        // Act
        $this->assertDatabaseHas('offers', $offer->toArray());
        $response = $this->actingAs($user)->deleteJson(route('offers.destroy', 1));


        // Assert
        $response->assertNoContent();
        $this->assertDatabaseMissing('offers', $offer->toArray());
    }

    /**
     * @test
     */
    public function destroy_offerIsNotDestroyedWhenNotOwner()
    {
        // Arrange
        $offer = factory(Offer::class)->create([
            // We do this because the timestamps are not equal when they are returned from the database.
            // This is normal behavior
            'created_at' => null,
            'updated_at' => null,
        ]); // A user is already created in this factory
        $user = factory(User::class)->create();

        // Act
        $response = $this->actingAs($user)->deleteJson(route('offers.destroy', 1));

        // Assert
        $this->assertDatabaseHas('offers', $offer->toArray());
        $response->assertStatus(401);
    }

    /**
     * @test
     */
    public function destroy_imagesDeletedWhenOfferIsDeleted()
    {
        // Arrange
        $response = $this->store_offerIsStoredWithImages();
        $offer = Offer::find($response['id']);
        $imagesToBeDeleted = $offer->images();
        $user = User::find(1);

        // Act
        $response = $this->actingAs($user)->deleteJson(route('offers.destroy', 1));
        $imagesInDatabaseAfterDelete = DB::table('images')->get();

        // Assert
        $this->assertEquals(0, count($imagesInDatabaseAfterDelete));

        $this->assertFileNotExists(public_path($imagesToBeDeleted[0]->path_to_image));
        $this->assertFileNotExists(public_path($imagesToBeDeleted[1]->path_to_image));
    }


    /**
     * @test
     */
    public function update_offerIsUpdatedWhenOwner()
    {
        // Arrange
        $offer = factory(Offer::class)->create([
            // We do this because the timestamps are not equal when they are returned from the database.
            // This is normal behavior
            'created_at' => null,
            'updated_at' => null,
        ]);
        $user = User::find(1);

        $updatedOffer = new Offer();
        $updatedOffer->id = 1;
        $updatedOffer->title = "title";
        $updatedOffer->description = "description";
        $updatedOffer->location = "location";
        $updatedOffer->price = 3.4;

        // Act
        $this->assertDatabaseHas('offers', $offer->toArray());
        $response = $this->actingAs($user)->patchJson(route('offers.update', 1), $updatedOffer->toArray());

        // Assert
        $response->assertOk();
        $this->assertDatabaseHas('offers', $updatedOffer->toArray());
    }

    /**
     * @test
     */
    public function update_offerNotUpdatedWhenNotOwner()
    {
        // Arrange
        $offer = factory(Offer::class)->create([
            // We do this because the timestamps are not equal when they are returned from the database.
            // This is normal behavior
            'created_at' => null,
            'updated_at' => null,
        ]);
        $user = factory(User::class)->create();

        // Act
        $this->assertDatabaseHas('offers', $offer->toArray());
        $response = $this->actingAs($user)->patchJson(route('offers.update', 1), $offer->toArray());

        // Assert
        $response->assertUnauthorized();
        $this->assertDatabaseHas('offers', $offer->toArray());
    }

    /**
     * @test
     */
    public function update_oldImagesAreDeletedWhenNewSupplied()
    {
        // Arrange
        $response = $this->store_offerIsStoredWithImages();
        $offer = Offer::find($response['id']);
        $imagesToBeDeleted = $offer->images();
        $user = User::find(1);

        Storage::fake('images');
        $image3 = 'image3.jpg';
        $image4 = 'image4.jpg';
        $newImages = [UploadedFile::fake()->image($image3), UploadedFile::fake()->image($image4)];

        $updatedOffer = new Offer();
        $updatedOffer->id = 1;
        $updatedOffer->title = "title";
        $updatedOffer->description = "description";
        $updatedOffer->location = "location";
        $updatedOffer->price = 3.4;
        $updatedOffer->images = $newImages;

        // Act
        $response = $this->actingAs($user)->patchJson(route('offers.update', 1), $updatedOffer->toArray());
        $offer = Offer::find($response['id']);
        $offerImages = $offer->images();


        // Assert
        $this->assertFileNotExists(public_path($imagesToBeDeleted[0]->path_to_image), 'Image not deleted from disk');
        $this->assertFileNotExists(public_path($imagesToBeDeleted[1]->path_to_image), 'Image not deleted from disk');

        $this->assertFileExists(public_path($offerImages[0]->path_to_image), 'Image not saved to disk');
        $this->assertFileExists(public_path($offerImages[1]->path_to_image), 'Image not saved to disk');

//        $image1Exists = Storage::disk('local')->exists($imagesToBeDeleted[0]->path_to_image);
//        $this->assertFalse($image1Exists, "Image not deleted from disk");
//        $image2Exists = Storage::disk('local')->exists($imagesToBeDeleted[1]->path_to_image);
//        $this->assertFalse($image2Exists, "Image not deleted from disk");
//        $image3Exists = Storage::disk('local')->exists($offerImages[0]->path_to_image);
//        $this->assertFalse($image2Exists, "Image not saved to disk");
//        $image4Exists = Storage::disk('local')->exists($offerImages[1]->path_to_image);
//        $this->assertFalse($image2Exists, "Image not saved to disk");
    }


}
