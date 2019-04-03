<?php

namespace Tests\Feature;

use App\Services\AlarmService;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AlarmTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testExample()
    {
        $AlarmService = app(AlarmService::class);

        for($i=0; $i<=5;$i++) {
            $message = [
                'project'=>'api-passport',
                'path'=>"/users",
                'value'=>500,
                'method'=>"GET",
                'request_id'=>rand(100,1000).$i,
                'type'=>1,
            ];
            info("message");
            info($message);
            $message = json_encode($message);
            $res = $AlarmService->message($message);
//            info($res);
            $this->assertTrue($res);
        }


//        $this->assertTrue(true);
    }
}
