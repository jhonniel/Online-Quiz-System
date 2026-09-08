<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Services\MailConfigService;
use App\Mail\HiringApplicationCredentials;
use App\Mail\HiringApplicationReconsideration;
use App\Mail\HiringApplicationStatusUpdate;
use App\Mail\HiringApplicationReceived;
use App\Models\HiringApplication;
use App\Models\HiringPosition;
use Carbon\Carbon;

class TestHiringApplicationEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:hiring-email 
                            {email : The email address to send the test email to}
                            {--type=credentials : Email type: credentials, reconsideration, rejection, hired, or received}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test hiring application email notifications';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $type = $this->option('type');

        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error("Invalid email address: {$email}");
            return 1;
        }

        // Configure mail settings
        MailConfigService::configure();

        $this->info("Testing {$type} email to: {$email}");
        $this->info("Mail Driver: " . config('mail.default'));
        $this->info("From Address: " . config('mail.from.address'));
        $this->newLine();

        try {
            // Create mock application data
            $mockApplication = $this->createMockApplication();
            $mockPosition = $this->createMockPosition();

            switch ($type) {
                case 'credentials':
                    $this->sendCredentialsEmail($email, $mockApplication, $mockPosition);
                    break;
                case 'reconsideration':
                    $this->sendReconsiderationEmail($email, $mockApplication, $mockPosition);
                    break;
                case 'rejection':
                    $this->sendRejectionEmail($email, $mockApplication, $mockPosition);
                    break;
                case 'hired':
                    $this->sendHiredEmail($email, $mockApplication, $mockPosition);
                    break;
                case 'received':
                    $this->sendReceivedEmail($email, $mockApplication, $mockPosition);
                    break;
                default:
                    $this->error("Unknown email type: {$type}");
                    $this->info("Available types: credentials, reconsideration, rejection, hired, received");
                    return 1;
            }

            $this->newLine();
            $this->info("✅ Test email sent successfully!");
            $this->info("Please check the inbox (and spam folder) for: {$email}");
            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Failed to send test email: " . $e->getMessage());
            $this->error("Stack trace: " . $e->getTraceAsString());
            return 1;
        }
    }

    private function createMockApplication()
    {
        return (object) [
            'id' => 999,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'full_name' => 'John Doe',
            'email' => 'test@example.com',
            'phone' => '+1234567890',
            'school' => 'Test University',
            'position_applied' => 'Software Developer',
            'status' => 'accepted',
            'interview_date' => Carbon::now()->addDays(7),
            'hiringPosition' => null,
        ];
    }

    private function createMockPosition()
    {
        return (object) [
            'id' => 999,
            'title' => 'Software Developer',
            'employment_type' => 'Full-time',
        ];
    }

    private function sendCredentialsEmail($email, $application, $position)
    {
        $application->hiringPosition = $position;
        $password = 'TestPassword123!';
        
        Mail::to($email)->send(new HiringApplicationCredentials(
            $application,
            $email,
            $password,
            $application->interview_date,
            $position
        ));

        $this->info("Sent: Hiring Application Credentials email");
    }

    private function sendReconsiderationEmail($email, $application, $position)
    {
        $application->hiringPosition = $position;
        $password = 'TestPassword123!';
        
        Mail::to($email)->send(new HiringApplicationReconsideration(
            $application,
            $email,
            $password,
            $application->interview_date,
            $position
        ));

        $this->info("Sent: Hiring Application Reconsideration email");
    }

    private function sendRejectionEmail($email, $application, $position)
    {
        $application->hiringPosition = $position;
        $adminNotes = 'This is a test rejection email.';
        
        Mail::to($email)->send(new HiringApplicationStatusUpdate(
            $application,
            'rejected',
            $adminNotes,
            $position
        ));

        $this->info("Sent: Hiring Application Rejection email");
    }

    private function sendHiredEmail($email, $application, $position)
    {
        $application->hiringPosition = $position;
        $application->start_date = Carbon::now()->addWeeks(2);
        $adminNotes = 'Please prepare your government IDs for onboarding.';

        Mail::to($email)->send(new HiringApplicationStatusUpdate(
            $application,
            'hired',
            $adminNotes,
            $position
        ));

        $this->info("Sent: Hiring Application Hired email");
    }

    private function sendReceivedEmail($email, $application, $position)
    {
        $application->hiringPosition = $position;
        
        Mail::to($email)->send(new HiringApplicationReceived(
            $application,
            $position
        ));

        $this->info("Sent: Hiring Application Received email (admin notification)");
    }
}
