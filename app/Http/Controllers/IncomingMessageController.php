<?php

namespace App\Http\Controllers;

use App\Models\IncomingMessage;
use Illuminate\Http\Request;
use App\DataTables\IncomingMessageDataTable;
use Illuminate\Support\Facades\DB;
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

    public function handleWebhook(Request $request) 
    {
        
        $verifyToken = env('WHATSAPP_VERIFY_TOKEN');
        if($request->input('hub_mode') === 'subscribe' && $request->input('hub_verify_token') === $verifyToken) {
            return $request->input('hub_challenge');
        } else {
            return response('Invalid token', 403);
        }

        $entry = $request->input('entry')[0] ?? null;
        
        if(!$entry || !isset($entry['changes'][0]['value']['messages'][0])) {
            return response('No message found', 422);
        }
        DB::beginTransaction();

        $messageData = $entry['changes'][0]['value']['messages'][0];
        $from = $messageData['from'];
        $type = $messageData['type'];
        $body = $type === 'text' ? $messageData['text']['body'] : null;
        $mediaUrl = null;

        if($type === 'image') {
            $mediaId = $messageData['image']['id'];
            $mediaUrl = Http::withToken(env('WHATSAPP_TOKEN'))->get('https://graph.facebook.com/v19.0/'.$mediaId);
            $mediaUrl = $mediaUrl->json()['url'] ?? null;

            if($mediaUrl) {
                $mediaResponse = Http::withToken(env('WHATSAPP_TOKEN'))->get($mediaUrl);
                $filename = 'ss_'.time().'.jpg';
                $incomingMessage = IncomingMessage::create([
                    'wa_id' => $messageData['id'] ?? null,
                    'from_number' => $from,
                    'message_body' => $body,
                    'message_type' => $type,
                    'media_url' => $mediaUrl,
                ]);
        
                Storage::disk('public')->put('screenshots/'.$filename, $mediaResponse->body());
                if($incomingMessage) {
                    $screenshot = Screenshot::create([
                        'incoming_message_id' => $incomingMessage->id,
                        'sender_number' => $from,
                        'image_path' => $filename,
                    ]);
                }
        
                DB::commit();
                return response()->json(['status' => 'image saved'], 200);
            }
            else {
                DB::rollBack();
                return response()->json(['status' => 'image not saved'], 400);
            }
        }

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
