<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ForumThread;
use App\Models\User;

class ForumThreadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get admin user
        $admin = User::where('role', 'admin')->first();

        if (!$admin) {
            $this->command->warn('No admin user found. Please create an admin user first.');
            return;
        }

        $threads = [
            [
                'title' => 'Welcome to Our Learning Community!',
                'content' => 'Welcome everyone to our online quiz platform! This is a place where you can test your knowledge, learn new things, and connect with fellow learners. We\'re excited to have you here and look forward to seeing your progress as you take on various quizzes and challenges.

Remember to:
- Take your time with each question
- Learn from your mistakes
- Share your experiences with others
- Have fun while learning!

If you have any questions or need help, don\'t hesitate to reach out to our support team. Happy learning!',
                'is_published' => true,
                'is_pinned' => true,
            ],
            [
                'title' => 'New Quiz Categories Added!',
                'content' => 'Great news! We\'ve added several new quiz categories to expand your learning opportunities:

🚀 **New Categories:**
- Advanced JavaScript
- Machine Learning Fundamentals
- Cloud Computing
- Cybersecurity Basics
- Data Structures & Algorithms

Each category contains carefully crafted questions designed to test and enhance your knowledge. Whether you\'re a beginner or an expert, there\'s something for everyone.

Start exploring these new categories and let us know what you think! We\'re always looking to improve and add more content based on your feedback.',
                'is_published' => true,
                'is_pinned' => false,
            ],
            [
                'title' => 'Study Tips for Better Performance',
                'content' => 'Here are some proven study tips to help you perform better on quizzes and retain information longer:

**1. Active Learning**
- Don\'t just read passively
- Take notes and summarize key points
- Practice explaining concepts to others

**2. Spaced Repetition**
- Review material at increasing intervals
- Don\'t cram everything at once
- Use the quiz results to identify weak areas

**3. Practice Tests**
- Take practice quizzes regularly
- Learn from your mistakes
- Focus on understanding, not memorization

**4. Healthy Study Habits**
- Take regular breaks
- Stay hydrated and well-rested
- Create a dedicated study space

**5. Join Study Groups**
- Connect with other learners
- Discuss difficult concepts
- Share resources and tips

Remember, learning is a journey, not a destination. Keep practicing and don\'t be discouraged by setbacks!',
                'is_published' => true,
                'is_pinned' => false,
            ],
            [
                'title' => 'Community Guidelines & Best Practices',
                'content' => 'To ensure a positive learning environment for everyone, please follow these community guidelines:

**Respectful Communication**
- Be kind and respectful to all members
- Use constructive feedback
- Avoid spam or off-topic discussions

**Academic Integrity**
- Complete quizzes independently
- Don\'t share answers during active quizzes
- Report any suspicious behavior

**Helpful Participation**
- Share useful resources and tips
- Help fellow learners when possible
- Ask thoughtful questions

**Privacy & Safety**
- Protect your personal information
- Report inappropriate content
- Respect others\' privacy

**Feedback & Suggestions**
- We welcome your feedback
- Use the feedback system for suggestions
- Help us improve the platform

By following these guidelines, we can create a supportive and productive learning community for everyone. Thank you for being part of our platform!',
                'is_published' => true,
                'is_pinned' => false,
            ],
            [
                'title' => 'Upcoming Features & Platform Updates',
                'content' => 'We\'re constantly working to improve your learning experience. Here\'s what\'s coming soon:

**🔄 Coming Soon:**
- **Mobile App**: Native mobile applications for iOS and Android
- **Offline Mode**: Take quizzes without internet connection
- **Progress Tracking**: Detailed analytics of your learning journey
- **Certificates**: Earn certificates upon completing quiz series
- **Leaderboards**: Friendly competition with other learners

**📊 Recent Updates:**
- Improved quiz interface
- Better performance tracking
- Enhanced search functionality
- Mobile-responsive design

**🎯 In Development:**
- AI-powered personalized recommendations
- Video explanations for complex topics
- Collaborative study rooms
- Advanced reporting for educators

We value your input! If you have suggestions for new features or improvements, please let us know through our feedback system. Your ideas help shape the future of our platform.',
                'is_published' => true,
                'is_pinned' => false,
            ],
        ];

        foreach ($threads as $threadData) {
            ForumThread::create([
                'admin_id' => $admin->id,
                'title' => $threadData['title'],
                'content' => $threadData['content'],
                'is_published' => $threadData['is_published'],
                'is_pinned' => $threadData['is_pinned'],
                'views_count' => rand(50, 500),
                'likes_count' => rand(10, 100),
                'comments_count' => rand(5, 50),
                'saves_count' => rand(5, 30),
                'shares_count' => rand(2, 20),
            ]);
        }

        $this->command->info('Forum threads seeded successfully!');
    }
}
