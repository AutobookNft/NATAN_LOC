<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\UserConversation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * @package App\Http\Controllers
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 1.0.0 (NATAN_LOC)
 * @date 2025-11-01
 * @purpose Controller per interfaccia chat NATAN con cronologia conversazioni
 */
class NatanChatController extends Controller
{
    /**
     * Show NATAN chat interface with conversation history
     * 
     * @return View
     */
    public function index(): View
    {
        $user = Auth::user();
        
        // Get chat history for current user (last 20 conversations, ordered by last_message_at DESC)
        $chatHistory = UserConversation::query()
            ->where('user_id', $user->id)
            ->where('type', 'natan_chat')
            ->orderBy('last_message_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get()
            ->map(function ($conversation) {
                return [
                    'id' => $conversation->conversation_id,
                    'title' => $conversation->title ?? __('natan.history.untitled'),
                    'date' => $conversation->last_message_at ?? $conversation->created_at,
                    'message_count' => $conversation->message_count,
                    'persona' => $conversation->persona ?? 'strategic',
                ];
            })
            ->toArray();
        
        return view('natan.chat', [
            'chatHistory' => $chatHistory,
        ]);
    }
}

