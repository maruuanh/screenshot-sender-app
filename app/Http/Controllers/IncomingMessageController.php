<?php

namespace App\Http\Controllers;

use App\Models\IncomingMessage;
use App\Models\Screenshot;
use App\DataTables\IncomingMessageDataTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class IncomingMessageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IncomingMessageDataTable $dataTable)
    {
        return $dataTable->render('incoming-message.index', [
            'title' => 'Incoming Messages',
        ]);
    }

    public function verifyWebhook(Request $request)
    {
        $verify_token = env('WHATSAPP_TOKEN');

        if (
            $request->get('hub_mode') === 'subscribe' &&
            $request->get('hub_verify_token') === $verify_token
        ) {
            return response($request->get('hub_challenge'), 200);
        }

        return response('Token mismatch', 403);
    }

    public function handleWebhook(Request $request)
    {
        Log::info('Webhook dipanggil!');
        Log::info(json_encode($request->all()));
        $entry = $request->input('entry')[0] ?? null;

        if (!$entry || !isset($entry['changes'][0]['value']['messages'][0])) {
            return response('No message found', 422);
        }
        DB::beginTransaction();

        $messageData = $entry['changes'][0]['value']['messages'][0];
        $from = $messageData['from'];
        $type = $messageData['type'];
        $body = $type === 'text' ? $messageData['text']['body'] : null;
        $mediaUrl = null;

        if ($type === 'image') {
            $mediaId = $messageData['image']['id'];
            $mediaUrl = Http::withToken(env('WHATSAPP_TOKEN'))->get('https://graph.facebook.com/v19.0/' . $mediaId);
            $mediaUrl = $mediaUrl->json()['url'] ?? null;

            if ($mediaUrl) {
                $mediaResponse = Http::withToken(env('WHATSAPP_TOKEN'))->get($mediaUrl);
                $filename = 'ss_' . time() . '.jpg';
                $incomingMessage = IncomingMessage::create([
                    'wa_id' => $messageData['id'] ?? null,
                    'from_number' => $from,
                    'message_body' => $body,
                    'message_type' => $type,
                    'media_url' => $mediaUrl,
                ]);

                Storage::disk('public')->put('screenshots/' . $filename, $mediaResponse->body());
                if ($incomingMessage) {
                    $screenshot = Screenshot::create([
                        'incoming_message_id' => $incomingMessage->id,
                        'sender_number' => $from,
                        'image_path' => $filename,
                    ]);
                }

                DB::commit();
            }
        }
        return response()->json(['status' => 'ok'], 200); // <-- ini penting!
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(IncomingMessage $incomingMessage)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(IncomingMessage $incomingMessage)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, IncomingMessage $incomingMessage)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(IncomingMessage $incomingMessage)
    {
        //
    }
}
