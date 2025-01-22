<?php

namespace Mhasnainjafri\RestApiKit\Tests\Unit;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Mhasnainjafri\RestApiKit\Http\Controllers\RestController;
use Mhasnainjafri\RestApiKit\Tests\TestCase;
use Mockery;

class RestControllerTest extends TestCase
{
    /** @test */
    public function it_generates_a_json_response()
    {
        $controller = new RestController();
        $data = ['key' => 'value'];
        $message = 'Success';

        $response = $controller->response($data, $message, 200);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(['data' => $data, 'message' => $message], $response->getData(true));
    }

}
