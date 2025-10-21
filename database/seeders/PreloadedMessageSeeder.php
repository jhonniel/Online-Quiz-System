<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PreloadedMessage;

class PreloadedMessageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $messages = [
            // Greetings
            [
                'title' => 'Hello! How can I help you?',
                'message' => 'Hello! Thank you for contacting us. How can I assist you today?',
                'category' => 'greeting',
                'sort_order' => 1,
            ],
            [
                'title' => 'Welcome to our support',
                'message' => 'Welcome! I\'m here to help you with any questions or issues you might have.',
                'category' => 'greeting',
                'sort_order' => 2,
            ],
            
            // Technical Support
            [
                'title' => 'Quiz not loading',
                'message' => 'I understand you\'re having trouble with a quiz not loading. Let me help you troubleshoot this issue. Can you please try refreshing the page or clearing your browser cache?',
                'category' => 'technical',
                'sort_order' => 1,
            ],
            [
                'title' => 'Login issues',
                'message' => 'I see you\'re having trouble logging in. Let me help you resolve this. Please try resetting your password or contact us if the issue persists.',
                'category' => 'technical',
                'sort_order' => 2,
            ],
            [
                'title' => 'Browser compatibility',
                'message' => 'For the best experience, please ensure you\'re using a modern browser like Chrome, Firefox, Safari, or Edge. Some features may not work properly in older browsers.',
                'category' => 'technical',
                'sort_order' => 3,
            ],
            
            // Quiz Related
            [
                'title' => 'Quiz code not working',
                'message' => 'I understand the quiz code isn\'t working. Please double-check the code and make sure there are no extra spaces. If the issue continues, please contact your instructor.',
                'category' => 'quiz',
                'sort_order' => 1,
            ],
            [
                'title' => 'Quiz submission issues',
                'message' => 'I see you\'re having trouble submitting your quiz. Please ensure all questions are answered and try submitting again. If the problem persists, let me know.',
                'category' => 'quiz',
                'sort_order' => 2,
            ],
            [
                'title' => 'Quiz results not showing',
                'message' => 'I understand your quiz results aren\'t displaying. This might be a temporary issue. Please try refreshing the page or contact your instructor for assistance.',
                'category' => 'quiz',
                'sort_order' => 3,
            ],
            
            // General Support
            [
                'title' => 'Account questions',
                'message' => 'I\'d be happy to help you with your account. What specific information do you need?',
                'category' => 'account',
                'sort_order' => 1,
            ],
            [
                'title' => 'Password reset',
                'message' => 'I can help you reset your password. Please check your email for reset instructions, or let me know if you need further assistance.',
                'category' => 'account',
                'sort_order' => 2,
            ],
            
            // Closing
            [
                'title' => 'Issue resolved',
                'message' => 'Great! I\'m glad we were able to resolve your issue. Is there anything else I can help you with today?',
                'category' => 'closing',
                'sort_order' => 1,
            ],
            [
                'title' => 'Thank you',
                'message' => 'Thank you for contacting us! If you have any other questions, feel free to reach out anytime.',
                'category' => 'closing',
                'sort_order' => 2,
            ],
            [
                'title' => 'Escalation needed',
                'message' => 'I understand this is a complex issue. Let me escalate this to our technical team who will be able to provide more specialized assistance.',
                'category' => 'escalation',
                'sort_order' => 1,
            ],
        ];

        foreach ($messages as $message) {
            PreloadedMessage::create($message);
        }
    }
}