<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;

class LineController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }
    public function testView()
    {
        //
        return view('line_test_view');
    }

    public function sendMessage(Request $request)
    {
        $ref = $request->input('ref'); // Capture the ref parameter from the URL
        $url = $request->input('url'); // Get the URL passed from the Blade template

        $lineUserId = 'U25fc5496e631e0cab0923f8f3cfc6db0'; // Replace this with the Line user ID (you'll need to collect this via webhook)

        $httpClient = new Client();
        $response = $httpClient->post('https://api.line.me/v2/bot/message/push', [
            'headers' => [
                'Authorization' => 'Bearer ' . env('LINE_ACCESS_TOKEN'), // Line Access Token from .env
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'to' => $lineUserId,
                'messages' => [
                    [
                        'type' => 'template',
                        'altText' => 'Contact Admin',
                        'template' => [
                            'type' => 'buttons',
                            'thumbnailImageUrl' => 'https://form.infinitebrain.co.th/images/infinite-c.png', // Thumbnail image URL
                            // 'thumbnailImageUrl' => asset('images/infinite-c.png'), // Thumbnail image URL
                            'title' => 'Contact Admin', // Title of the message
                            'text' => 'your ref : ' . $ref, // Text in the message
                            'actions' => [
                                [
                                    'type' => 'uri', // Link to a website
                                    'label' => 'URL for your order',
                                    'uri' => $url // Use the captured URL from the request
                                ],
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        // return response()->json(['status' => 'Message sent']); // i want return back or $url
        $addLineUrl = 'https://line.me/R/ti/p/@352vgbrf?ref=' . $ref;
        return redirect($addLineUrl);
    }

    public function webhook(Request $request)
    {
        $events = $request->input('events');
        $ref = $request->input('ref'); // Capture the ref parameter if sent

        foreach ($events as $event) {
            if ($event['type'] === 'follow') {
                $userId = $event['source']['userId']; // Get the user's Line ID
                // $ref = $request->input('ref'); // Capture the ref parameter if sent

                // Now send a reply with the referral code
                $this->sendReferralMessage($userId, $ref);
            }
        }

        return response()->json(['status' => 'Webhook received']);
    }

    // public function webhook(Request $request)
    // {
    //     return response()->json(['status' => 'Webhook received'], 200);
    // }

    public function sendReferralMessage($lineUserId, $ref)
    {
        $httpClient = new Client();
        $response = $httpClient->post('https://api.line.me/v2/bot/message/push', [
            'headers' => [
                'Authorization' => 'Bearer ' . env('LINE_ACCESS_TOKEN'),
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'to' => $lineUserId,
                'messages' => [
                    [
                        'type' => 'template',
                        'altText' => 'Contact Admin',
                        'template' => [
                            'type' => 'buttons',
                            'thumbnailImageUrl' => 'https://form.infinitebrain.co.th/images/infinite-c.png', // Thumbnail image URL
                            // 'thumbnailImageUrl' => asset('images/infinite-c.png'), // Thumbnail image URL
                            'title' => 'Contact Admin', // Title of the message
                            'text' => 'your ref : ' . $ref, // Text in the message
                            'actions' => [
                                [
                                    'type' => 'uri', // Link to a website
                                    'label' => 'URL for your order',
                                    // 'uri' => $url // Use the captured URL from the request
                                ],
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        return response()->json(['status' => 'Referral message sent']);
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
