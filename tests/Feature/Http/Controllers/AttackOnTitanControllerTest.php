<?php

namespace Tests\Feature\Http\Controllers;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AttackOnTitanControllerTest extends TestCase
{
    public function test_homepage_renders_the_attack_on_titan_collections(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('Attack on Titan API')
            ->assertSee('rel="stylesheet"', false)
            ->assertSee('id="collection-form"', false)
            ->assertSee('id="result-filter"', false)
            ->assertSee('id="results"', false)
            ->assertSee('Personajes')
            ->assertSee('Episodios')
            ->assertSee('Titanes');
    }

    public function test_collection_endpoint_returns_the_api_json_and_requests_the_selected_page(): void
    {
        Http::preventStrayRequests();

        $apiResponse = [
            'info' => [
                'count' => 201,
                'pages' => 11,
                'next_page' => 'https://api.attackontitanapi.com/characters?page=3',
                'prev_page' => 'https://api.attackontitanapi.com/characters?page=1',
            ],
            'results' => [
                ['id' => 1, 'name' => 'Armin Arlelt'],
            ],
        ];

        Http::fake([
            'api.attackontitanapi.com/characters*' => Http::response($apiResponse),
        ]);

        $response = $this->getJson('/api/attack-on-titan/characters?page=2');

        $response->assertOk()
            ->assertHeader('content-type', 'application/json')
            ->assertExactJson($apiResponse);

        Http::assertSent(function (Request $request): bool {
            parse_str((string) parse_url($request->url(), PHP_URL_QUERY), $query);

            return parse_url($request->url(), PHP_URL_PATH) === '/characters'
                && $query === ['page' => '2'];
        });
    }

    public function test_collection_endpoint_returns_json_when_the_api_connection_fails(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'api.attackontitanapi.com/characters*' => Http::failedConnection(),
        ]);

        $response = $this->getJson('/api/attack-on-titan/characters');

        $response->assertStatus(504)
            ->assertExactJson([
                'message' => 'La API de Attack on Titan no respondió. Inténtalo de nuevo más tarde.',
            ]);
    }

    public function test_unknown_collection_returns_not_found(): void
    {
        Http::preventStrayRequests();

        $response = $this->getJson('/api/attack-on-titan/unknown');

        $response->assertNotFound();
    }

    public function test_collection_endpoint_rejects_an_invalid_page(): void
    {
        Http::preventStrayRequests();

        $response = $this->getJson('/api/attack-on-titan/characters?page=0');

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('page');
    }
}
