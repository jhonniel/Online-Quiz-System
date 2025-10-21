<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Feedback;
use App\Models\User;

class FeedbackSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get some users to create feedback for
        $users = User::where('role', 'user')->take(5)->get();

        if ($users->count() == 0) {
            $this->command->info('No users found. Please create some users first.');
            return;
        }

        $sampleFeedbacks = [
            [
                'type' => 'bug_report',
                'title' => 'Quiz timer not working properly',
                'description' => 'When I take a quiz, the timer sometimes resets when I refresh the page. This is confusing and affects my quiz experience. The timer should continue from where it left off.',
                'priority' => 'high',
                'status' => 'pending',
            ],
            [
                'type' => 'feature_request',
                'title' => 'Add dark mode support',
                'description' => 'It would be great to have a dark mode option for the system. Many users prefer dark themes, especially when studying late at night. This would improve user experience significantly.',
                'priority' => 'medium',
                'status' => 'in_review',
                'admin_response' => 'Thank you for this suggestion! We are currently evaluating the implementation of dark mode. This feature is on our roadmap for the next major update.',
            ],
            [
                'type' => 'improvement',
                'title' => 'Better quiz result visualization',
                'description' => 'The current quiz results page could be improved with better charts and graphs. It would be helpful to see performance trends over time and compare with other students.',
                'priority' => 'medium',
                'status' => 'completed',
                'admin_response' => 'Great suggestion! We have implemented enhanced quiz result visualization with charts and performance analytics. You can now see your progress over time and compare with peers.',
            ],
            [
                'type' => 'bug_report',
                'title' => 'Mobile responsiveness issues',
                'description' => 'The quiz interface is not fully responsive on mobile devices. Some buttons are too small to tap easily, and the text is sometimes cut off on smaller screens.',
                'priority' => 'critical',
                'status' => 'in_progress',
                'admin_response' => 'We are aware of these mobile responsiveness issues and are working on a comprehensive mobile optimization update. This is our top priority.',
            ],
            [
                'type' => 'feature_request',
                'title' => 'Add quiz categories and tags',
                'description' => 'It would be helpful to organize quizzes by categories and add tags for easier searching. For example, categories like "Programming", "Mathematics", "Science" etc.',
                'priority' => 'low',
                'status' => 'pending',
            ],
            [
                'type' => 'general',
                'title' => 'Great system overall!',
                'description' => 'I really enjoy using this quiz system. The interface is clean and intuitive. The live chat feature is particularly helpful when I need assistance. Keep up the great work!',
                'priority' => 'low',
                'status' => 'completed',
                'admin_response' => 'Thank you for your positive feedback! We are glad you are enjoying the system. Your kind words motivate us to continue improving.',
            ],
            [
                'type' => 'improvement',
                'title' => 'Add keyboard shortcuts',
                'description' => 'It would be convenient to have keyboard shortcuts for common actions like submitting quizzes, navigating between questions, etc. This would speed up the quiz-taking process.',
                'priority' => 'medium',
                'status' => 'pending',
            ],
            [
                'type' => 'bug_report',
                'title' => 'Profile picture upload not working',
                'description' => 'I tried to upload a profile picture but it keeps showing an error message. The file is under 2MB and in JPG format as specified in the requirements.',
                'priority' => 'high',
                'status' => 'in_review',
                'admin_response' => 'We are investigating this issue with profile picture uploads. Our technical team is working on a fix and will update you soon.',
            ],
        ];

        foreach ($sampleFeedbacks as $index => $feedbackData) {
            $user = $users[$index % $users->count()];

            $feedback = Feedback::create([
                'user_id' => $user->id,
                'type' => $feedbackData['type'],
                'title' => $feedbackData['title'],
                'description' => $feedbackData['description'],
                'priority' => $feedbackData['priority'],
                'status' => $feedbackData['status'],
                'admin_response' => $feedbackData['admin_response'] ?? null,
                'admin_responded_at' => isset($feedbackData['admin_response']) ? now() : null,
                'created_at' => now()->subDays(rand(1, 30)),
                'updated_at' => now()->subDays(rand(0, 5)),
            ]);
        }

        $this->command->info('Sample feedback data created successfully!');
    }
}
