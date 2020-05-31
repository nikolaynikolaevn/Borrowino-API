<?php

namespace Tests\Feature;

use App\Image;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkaroundUserControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function images_getUserImage()
    {
        // Arrange
        $user = factory(User::class)->create();

        $image1 = 'image1.jpg';
        factory(Image::class)->create([
            'resource_id' => $user->id,
            'type' => 'profile_image',
            'path_to_image' => 'images/' . $image1,
        ]);

        // Act
        $response = $this->getJson(route('users.images', compact('user')));

        // Assert
        $response->assertOk();
        $this->assertStringContainsString($image1, $response['images'][0], $image1 . ' was not found in response array');
    }

    /**
     * @test
     */
    public function images_return404whenNoImagesFound()
    {
        // Arrange
        factory(User::class);

        // Act
        $response = $this->getJson(route('users.images', 1));

        // Assert
        $response->assertNotFound();
    }
}
