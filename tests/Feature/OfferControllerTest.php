<?php

namespace Tests\Feature;

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
    private $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = factory(User::class)->create();

        // Get all files in a directory
        $files = Storage::disk('local')->allFiles('images');

        // Delete Files
        Storage::delete($files);

    }

    use RefreshDatabase;


    /**
     * @test
     */
    public function index_returnAllOffers()
    {
        // Arrange
        $offersExpected = factory(Offer::class, 2)->create();

        // Act

        // Assert
        $this->actingAs($this->user, 'api')->getJson(route('offers.index'))
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['*' => ['id', 'created_at', 'updated_at', 'title', 'description', 'expires', 'location', 'price', 'owner']]
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
        $response = $this->actingAs($this->user, 'api')->get(route('offers.index') . '?' . $queryParameters); // this is a custom function. you can use $this->get(...)

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
        $response = $this->actingAs($this->user, 'api')->getJson(route('offers.show', $offerExpected->id));

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

        //        $user = factory(User::class)->create();

        // Act
        $response = $this->actingAs($this->user, 'api')->postJson(route('offers.store'), [
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

        //        $user = factory(User::class)->create();
        $image1 = 'image1.jpg';
        $image2 = 'image2.jpg';

        // Act
        $response = $this->actingAs($this->user, 'api')->postJson(route('offers.store'), [
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

        $this->assertFileExists(public_path(rawurldecode($imagesInDatabase[0]->path_to_image)));
        $this->assertFileExists(public_path(rawurldecode($imagesInDatabase[1]->path_to_image)));

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
            'owner' => $this->user->id,
        ]);
        //        $user = User::find(1);


        // Act
        $this->assertDatabaseHas('offers', $offer->toArray());
        $response = $this->actingAs($this->user, 'api')->deleteJson(route('offers.destroy', $offer->id));


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
        //        $user = factory(User::class)->create();

        // Act
        $response = $this->actingAs($this->user, 'api')->deleteJson(route('offers.destroy', $offer->id));

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
        //        $user = User::find(1);

        // Act
        $response = $this->actingAs($this->user, 'api')->deleteJson(route('offers.destroy', $offer->id));
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
            'owner' => $this->user->id,
        ]);
        //        $user = User::find(1);

        $updatedOffer = new Offer();
        $updatedOffer->title = "title";
        $updatedOffer->description = "description";
        $updatedOffer->location = "location";
        $updatedOffer->price = 3.4;

        // Act
        $this->assertDatabaseHas('offers', $offer->toArray());
        $response = $this->actingAs($this->user, 'api')->patchJson(route('offers.update', $offer->id), $updatedOffer->toArray());

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
        //        $user = factory(User::class)->create();

        // Act
        $this->assertDatabaseHas('offers', $offer->toArray());
        $response = $this->actingAs($this->user, 'api')->patchJson(route('offers.update', $offer->id), $offer->toArray());

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
//        $user = User::find(1);

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
        $response = $this->actingAs($this->user, 'api')->patchJson(route('offers.update', $offer->id), $updatedOffer->toArray());
        $offer = Offer::find($response['id']);
        $offerImages = $offer->images();


        // Assert
        $this->assertFileNotExists(public_path($imagesToBeDeleted[0]->path_to_image), 'Image not deleted from disk');
        $this->assertFileNotExists(public_path($imagesToBeDeleted[1]->path_to_image), 'Image not deleted from disk');

        $this->assertFileExists(public_path(rawurldecode($offerImages[0]->path_to_image)), 'Image not saved to disk');
        $this->assertFileExists(public_path(rawurldecode($offerImages[1]->path_to_image)), 'Image not saved to disk');
    }

    /**
     * @test
     */
    public function images_fetchUrlsWhenOfferHasImages()
    {
        // Arrange
        $image1 = 'image1.jpg';
        $image2 = 'image2.jpg';
        $response = $this->store_offerIsStoredWithImages();
        $offer = $response['id'];

        // Act
        $response = $this->actingAs($this->user, 'api')->getJson(route('offers.images', compact('offer')));

        // Assert
        $response->assertOk();
        $this->assertStringContainsString($image1, $response['images'][0], $image1 . ' was not found in response array');
        $this->assertStringContainsString($image2, $response['images'][1], $image2 . ' was not found in response array');
    }

    /**
     * @test
     */
    public function images_return404whenNoImagesFound()
    {
        // Arrange
        factory(Offer::class);

        // Act
        $response = $this->getJson(route('offers.images', 1));

        // Assert
        $response->assertNotFound();
    }


}
