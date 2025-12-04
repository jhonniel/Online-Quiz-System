<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RedirectController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\Auth\RoleLoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\QuizController as AdminQuizController;
use App\Http\Controllers\Admin\SettingsController as AdminSettingsController;
use App\Http\Controllers\Admin\ImportController as AdminImportController;
use App\Http\Controllers\Admin\UniversityController;
use App\Http\Controllers\Admin\ContactMessageController as AdminContactMessageController;
use App\Http\Controllers\Admin\LiveChatController as AdminLiveChatController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\User\DashboardController as UserDashboardController;
use App\Http\Controllers\User\QuizController as UserQuizController;
use Illuminate\Support\Facades\Route;

// Landing Page Routes
Route::get('/', [LandingController::class, 'index'])->name('landing.index');
Route::get('/features', [LandingController::class, 'features'])->name('landing.features');
Route::get('/about', [LandingController::class, 'about'])->name('landing.about');
Route::get('/contact', [LandingController::class, 'contact'])->name('landing.contact');
Route::post('/contact', [LandingController::class, 'storeContact'])->name('landing.contact.store');

// Public Hiring Application Routes (dynamic URL based on admin settings)
// The route will be registered dynamically in the controller based on settings
Route::get('/hiring/accept/{token}', [App\Http\Controllers\HiringApplicationController::class, 'acceptWithToken'])->name('hiring.accept');
Route::get('/hiring/application/success', [App\Http\Controllers\HiringApplicationController::class, 'success'])->name('hiring.application.success');

// Role-based Login Routes
Route::get('/login', [RoleLoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [RoleLoginController::class, 'login']);
Route::post('/logout', [RoleLoginController::class, 'logout'])->name('logout');

// Include Auth Routes (Password Reset, etc.)
require __DIR__.'/auth.php';

// Redirect authenticated users
Route::get('/home', [RedirectController::class, 'home'])->name('home');

// Admin Routes
Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/activity-data', [DashboardController::class, 'getActivityData'])->name('admin.activity-data');

    // User Management
    Route::get('users/api', [AdminUserController::class, 'api'])->name('admin.users.api');
    Route::resource('users', AdminUserController::class);
    Route::patch('users/{user}/toggle-status', [AdminUserController::class, 'toggleStatus'])->name('admin.users.toggle-status');
    Route::patch('users/{user}/approve', [AdminUserController::class, 'approve'])->name('admin.users.approve');
    Route::patch('users/{user}/disapprove', [AdminUserController::class, 'disapprove'])->name('admin.users.disapprove');
    Route::patch('users/{user}/overtime-window', [AdminUserController::class, 'updateOvertimeWindow'])->name('admin.users.overtime-window');
    Route::patch('users/{user}/leave-balance', [AdminUserController::class, 'updateLeaveBalance'])->name('admin.users.leave-balance');
    Route::post('users/bulk-assign-role', [AdminUserController::class, 'bulkAssignRole'])->name('admin.users.bulk-assign-role');

    // University Management
    Route::resource('universities', UniversityController::class);
    Route::patch('universities/{university}/toggle-status', [UniversityController::class, 'toggleStatus'])->name('admin.universities.toggle-status');

    // Quiz Management
    Route::resource('quizzes', AdminQuizController::class);
    Route::get('quizzes/{quiz}/export-history/pdf', [AdminQuizController::class, 'exportQuizHistoryPdf'])->name('admin.quizzes.export-history-pdf');
    Route::post('quizzes/{quiz}/assign', [AdminQuizController::class, 'assignToUsers'])->name('admin.quizzes.assign');
    Route::get('quizzes/{quiz}/assigned-users', [AdminQuizController::class, 'getAssignedUsers'])->name('admin.quizzes.assigned-users');
    Route::get('quizzes/{quiz}/results', [AdminQuizController::class, 'results'])->name('admin.quizzes.results');
    Route::post('quizzes/{quiz}/import-questions', [AdminQuizController::class, 'importQuestions'])->name('admin.quizzes.import-questions');
    Route::get('quizzes/template/download', [AdminQuizController::class, 'downloadTemplate'])->name('admin.quizzes.template.download');

    // Quiz Assignment Management
    Route::post('quiz-assignments/{assignment}/reset', [AdminQuizController::class, 'resetAssignment'])->name('admin.quiz-assignments.reset');
    Route::get('quiz-assignments/{assignment}/history', [AdminQuizController::class, 'viewAttemptHistory'])->name('admin.quiz-assignments.history');
    Route::get('quiz-assignments/{assignment}/history/pdf', [AdminQuizController::class, 'exportAttemptHistoryPdf'])->name('admin.quiz-assignments.history.pdf');
    Route::post('quiz-assignments/{assignment}/allow-retake', [AdminQuizController::class, 'allowRetake'])->name('admin.quiz-assignments.allow-retake');
    Route::get('quiz-attempts/{attemptId}/details', [AdminQuizController::class, 'getAttemptDetails'])->name('admin.quiz-attempts.details');
    Route::get('quizzes/{quizId}/users/{userId}/history', [AdminQuizController::class, 'getUserQuizHistory'])->name('admin.quizzes.user-history');

    // Manual Grading
    Route::get('manual-grading', [AdminQuizController::class, 'manualGrading'])->name('admin.manual-grading');
    Route::get('all-text-attempts', [AdminQuizController::class, 'allTextAttempts'])->name('admin.all-text-attempts');
    Route::post('quiz-attempts/{attempt}/grade', [AdminQuizController::class, 'gradeAttempt'])->name('admin.quiz-attempts.grade');

    // Quiz Import Routes
    Route::get('quizzes/import/form', [AdminQuizController::class, 'importForm'])->name('admin.quizzes.import-form');
    Route::post('quizzes/import', [AdminQuizController::class, 'import'])->name('admin.quizzes.import');
    Route::get('quizzes/import/template', [AdminQuizController::class, 'downloadTemplate'])->name('admin.quizzes.download-template');

    // Settings Management
    Route::get('/settings', [AdminSettingsController::class, 'index'])->name('admin.settings.index');
    Route::post('/settings', [AdminSettingsController::class, 'update'])->name('admin.settings.update');
    Route::get('/settings/health', [AdminSettingsController::class, 'getHealth'])->name('admin.settings.health');
    Route::post('/settings/test-email', [AdminSettingsController::class, 'testEmail'])->name('admin.settings.test-email');

    // DTR Management (Employees)
    Route::get('/dtr', [App\Http\Controllers\Admin\DtrController::class, 'index'])->name('admin.dtr.index');
    Route::get('/dtr/create', [App\Http\Controllers\Admin\DtrController::class, 'create'])->name('admin.dtr.create');
    Route::post('/dtr', [App\Http\Controllers\Admin\DtrController::class, 'store'])->name('admin.dtr.store');
    Route::get('/dtr/{dtr}/edit', [App\Http\Controllers\Admin\DtrController::class, 'edit'])->name('admin.dtr.edit');
    Route::put('/dtr/{dtr}', [App\Http\Controllers\Admin\DtrController::class, 'update'])->name('admin.dtr.update');
    Route::post('/dtr/import', [App\Http\Controllers\Admin\DtrController::class, 'import'])->name('admin.dtr.import');
    Route::get('/dtr/template', [App\Http\Controllers\Admin\DtrController::class, 'downloadTemplate'])->name('admin.dtr.template');

    // Student DTR Management
    Route::get('/student-dtr', [App\Http\Controllers\Admin\DtrController::class, 'studentIndex'])->name('admin.student-dtr.index');
    Route::get('/student-dtr/create', [App\Http\Controllers\Admin\DtrController::class, 'studentCreate'])->name('admin.student-dtr.create');
    Route::post('/student-dtr', [App\Http\Controllers\Admin\DtrController::class, 'studentStore'])->name('admin.student-dtr.store');
    Route::get('/student-dtr/{dtr}/edit', [App\Http\Controllers\Admin\DtrController::class, 'studentEdit'])->name('admin.student-dtr.edit');
    Route::put('/student-dtr/{dtr}', [App\Http\Controllers\Admin\DtrController::class, 'studentUpdate'])->name('admin.student-dtr.update');
    Route::get('/student-dtr/export/pdf', [App\Http\Controllers\Admin\DtrController::class, 'studentExportPdf'])->name('admin.student-dtr.export-pdf');

    // Leave Requests Management (Employees)
    Route::get('/leave-requests', [App\Http\Controllers\Admin\LeaveRequestController::class, 'index'])->name('admin.leave-requests.index');
    Route::get('/leave-calendar', [App\Http\Controllers\Admin\LeaveRequestController::class, 'calendar'])->name('admin.leave-requests.calendar');
    Route::get('/leave-requests/{leaveRequest}', [App\Http\Controllers\Admin\LeaveRequestController::class, 'show'])->name('admin.leave-requests.show');
    Route::post('/leave-requests/{leaveRequest}/approve', [App\Http\Controllers\Admin\LeaveRequestController::class, 'approve'])->name('admin.leave-requests.approve');
    Route::post('/leave-requests/{leaveRequest}/reject', [App\Http\Controllers\Admin\LeaveRequestController::class, 'reject'])->name('admin.leave-requests.reject');
    Route::post('/leave-requests/{leaveRequest}/resubmit', [App\Http\Controllers\Admin\LeaveRequestController::class, 'resubmit'])->name('admin.leave-requests.resubmit');

    // Student Leave Requests Management
    Route::get('/student-leave-requests', [App\Http\Controllers\Admin\LeaveRequestController::class, 'studentIndex'])->name('admin.student-leave-requests.index');
    Route::get('/student-leave-calendar', [App\Http\Controllers\Admin\LeaveRequestController::class, 'studentCalendar'])->name('admin.student-leave-requests.calendar');

    // Hiring Process Management
    Route::get('/hiring-process', [App\Http\Controllers\Admin\HiringProcessController::class, 'index'])->name('admin.hiring-process.index');
    Route::get('/hiring-process/applicants', [App\Http\Controllers\Admin\HiringProcessController::class, 'applicants'])->name('admin.hiring-process.applicants');

    // Hiring Positions Management
    Route::resource('hiring-positions', App\Http\Controllers\Admin\HiringPositionController::class)->names('admin.hiring-positions');
    Route::patch('hiring-positions/{hiringPosition}/toggle-status', [App\Http\Controllers\Admin\HiringPositionController::class, 'toggleStatus'])->name('admin.hiring-positions.toggle-status');

    // Hiring Applications Management
    Route::get('/hiring-applications', [App\Http\Controllers\Admin\HiringApplicationController::class, 'index'])->name('admin.hiring-applications.index');
    Route::get('/hiring-applications/{application}', [App\Http\Controllers\Admin\HiringApplicationController::class, 'show'])->name('admin.hiring-applications.show');
    Route::post('/hiring-applications/{application}/accept', [App\Http\Controllers\Admin\HiringApplicationController::class, 'accept'])->name('admin.hiring-applications.accept');
    Route::post('/hiring-applications/{application}/reject', [App\Http\Controllers\Admin\HiringApplicationController::class, 'reject'])->name('admin.hiring-applications.reject');
    Route::post('/hiring-applications/{application}/schedule-interview', [App\Http\Controllers\Admin\HiringApplicationController::class, 'scheduleInterview'])->name('admin.hiring-applications.schedule-interview');
    Route::get('/hiring-applications/{application}/download-resume', [App\Http\Controllers\Admin\HiringApplicationController::class, 'downloadResume'])->name('admin.hiring-applications.download-resume');
    Route::delete('/hiring-applications/{application}', [App\Http\Controllers\Admin\HiringApplicationController::class, 'destroy'])->name('admin.hiring-applications.destroy');

    // Forum Management
    Route::resource('forum', App\Http\Controllers\Admin\ForumController::class)->names('admin.forum');
    Route::patch('forum/{forum}/toggle-publish', [App\Http\Controllers\Admin\ForumController::class, 'togglePublish'])->name('admin.forum.toggle-publish');
    Route::patch('forum/{forum}/toggle-pin', [App\Http\Controllers\Admin\ForumController::class, 'togglePin'])->name('admin.forum.toggle-pin');
    Route::post('forum/comment', [App\Http\Controllers\Admin\ForumController::class, 'comment'])->name('admin.forum.comment');
    Route::post('forum/comment/like', [App\Http\Controllers\Admin\ForumController::class, 'likeComment'])->name('admin.forum.comment.like');

    // Notification Management
    Route::get('notifications/recent', [App\Http\Controllers\Admin\NotificationController::class, 'getRecent'])->name('admin.notifications.recent');
    Route::get('notifications/unread-count', [App\Http\Controllers\Admin\NotificationController::class, 'getUnreadCount'])->name('admin.notifications.unread-count');
    Route::post('notifications/mark-read', [App\Http\Controllers\Admin\NotificationController::class, 'markAsRead'])->name('admin.notifications.mark-read');
    Route::post('notifications/send-to-all', [App\Http\Controllers\Admin\NotificationController::class, 'sendToAll'])->name('admin.notifications.send-to-all');
    Route::post('notifications/bulk-delete', [App\Http\Controllers\Admin\NotificationController::class, 'bulkDelete'])->name('admin.notifications.bulk-delete');
    Route::post('notifications/mark-all-read', [App\Http\Controllers\Admin\NotificationController::class, 'markAllAsRead'])->name('admin.notifications.mark-all-read');
    Route::get('notifications/stats', [App\Http\Controllers\Admin\NotificationController::class, 'getStats'])->name('admin.notifications.stats');
    Route::resource('notifications', App\Http\Controllers\Admin\NotificationController::class)->names('admin.notifications');

    // Contact Messages Management
    Route::resource('contact-messages', AdminContactMessageController::class)->only(['index', 'show', 'destroy']);
    Route::post('contact-messages/{contactMessage}/reply', [AdminContactMessageController::class, 'reply'])->name('contact-messages.reply');
    Route::patch('contact-messages/{contactMessage}/close', [AdminContactMessageController::class, 'close'])->name('contact-messages.close');

    // Live Chat Management
    Route::get('live-chat', [AdminLiveChatController::class, 'index'])->name('live-chat.index');
    Route::get('live-chat/{ticketNumber}', [AdminLiveChatController::class, 'show'])->name('live-chat.show');

    // User Activity Management
    Route::get('user-activity', [App\Http\Controllers\Admin\UserActivityController::class, 'index'])->name('admin.user-activity.index');
    Route::get('user-activity/sessions', [App\Http\Controllers\Admin\UserActivityController::class, 'sessions'])->name('admin.user-activity.sessions');
    Route::get('user-activity/statistics', [App\Http\Controllers\Admin\UserActivityController::class, 'statistics'])->name('admin.user-activity.statistics');
    Route::post('user-activity/cleanup', [App\Http\Controllers\Admin\UserActivityController::class, 'cleanup'])->name('admin.user-activity.cleanup');
    Route::post('live-chat/{ticketNumber}/message', [AdminLiveChatController::class, 'store'])->name('live-chat.store');
    Route::get('live-chat/{ticketNumber}/messages', [AdminLiveChatController::class, 'getMessages'])->name('live-chat.messages');
    Route::get('live-chat/{ticketNumber}/new-messages', [AdminLiveChatController::class, 'getNewMessages'])->name('live-chat.new-messages');
    Route::get('live-chat/unread-count', [AdminLiveChatController::class, 'getUnreadCount'])->name('live-chat.unread-count');
    Route::post('live-chat/{ticketNumber}/close', [AdminLiveChatController::class, 'closeTicket'])->name('live-chat.close');
    Route::post('live-chat/{ticketNumber}/reopen', [AdminLiveChatController::class, 'reopenTicket'])->name('live-chat.reopen');
    Route::post('live-chat/{ticketNumber}/deny-reopen', [AdminLiveChatController::class, 'denyReopen'])->name('live-chat.deny-reopen');
    Route::get('live-chat/preloaded-messages', [AdminLiveChatController::class, 'getPreloadedMessages'])->name('live-chat.preloaded-messages');
    Route::post('live-chat/{ticketNumber}/typing/start', [AdminLiveChatController::class, 'startTyping'])->name('live-chat.typing.start');
    Route::post('live-chat/{ticketNumber}/typing/stop', [AdminLiveChatController::class, 'stopTyping'])->name('live-chat.typing.stop');
    Route::get('live-chat/{ticketNumber}/typing', [AdminLiveChatController::class, 'getTypingIndicators'])->name('live-chat.typing');

    // Status Management
    Route::get('status/online-users', [StatusController::class, 'getOnlineUsers'])->name('status.online-users');
    Route::get('status/away-users', [StatusController::class, 'getAwayUsers'])->name('status.away-users');
    Route::get('status/idle-users', [StatusController::class, 'getIdleUsers'])->name('status.idle-users');
    Route::get('status/all-users', [StatusController::class, 'getAllUserStatuses'])->name('status.all-users');

        // Analytics Dashboard
        Route::get('analytics', [App\Http\Controllers\Admin\AnalyticsController::class, 'index'])->name('admin.analytics.index');
        Route::get('analytics/quiz/{quizId}', [App\Http\Controllers\Admin\AnalyticsController::class, 'getQuizDetails'])->name('admin.analytics.quiz-details');
        Route::get('analytics/student/{userId}', [App\Http\Controllers\Admin\AnalyticsController::class, 'getStudentDetails'])->name('admin.analytics.student-details');
        Route::get('analytics/topic/{topic}', [App\Http\Controllers\Admin\AnalyticsController::class, 'getTopicDetails'])->name('admin.analytics.topic-details')->where('topic', '.*');

        // Feedback Management
        Route::resource('feedback', App\Http\Controllers\Admin\FeedbackController::class)->names('admin.feedback');
        Route::post('feedback/{feedback}/assign', [App\Http\Controllers\Admin\FeedbackController::class, 'assign'])->name('admin.feedback.assign');
        Route::get('feedback-stats', [App\Http\Controllers\Admin\FeedbackController::class, 'getStats'])->name('admin.feedback.stats');
        Route::get('feedback-admins', [App\Http\Controllers\Admin\FeedbackController::class, 'getAdmins'])->name('admin.feedback.admins');
    });

// User Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('user.dashboard');
    Route::get('/quizzes', [UserQuizController::class, 'index'])->name('user.quizzes.index');
    Route::get('/quizzes/enter-code', [UserQuizController::class, 'enterCode'])->name('user.quizzes.enter-code');
    Route::post('/quizzes/validate-code', [UserQuizController::class, 'validateCode'])->name('user.quizzes.validate-code');
    Route::get('/quizzes/{quiz}/take', [UserQuizController::class, 'take'])->name('user.quizzes.take');
    Route::post('/quizzes/{quiz}/submit', [UserQuizController::class, 'submit'])->name('user.quizzes.submit');
    Route::post('/quizzes/{quiz}/cancel', [UserQuizController::class, 'cancel'])->name('user.quizzes.cancel');
    Route::get('/quizzes/{quiz}/result', [UserQuizController::class, 'result'])->name('user.quizzes.result');
    Route::get('/quizzes/{quiz}/time-expired', [UserQuizController::class, 'timeExpired'])->name('user.quizzes.time-expired');

    // Chat Routes
    Route::get('/chat/messages', [ChatController::class, 'index'])->name('chat.messages');
    Route::post('/chat/messages', [ChatController::class, 'store'])->name('chat.store');
    Route::post('/chat/create', [ChatController::class, 'createTicket'])->name('chat.create');
    Route::post('/chat/mark-read', [ChatController::class, 'markAsRead'])->name('chat.mark-read');
    Route::get('/chat/unread-count', [ChatController::class, 'getUnreadCount'])->name('chat.unread-count');
    Route::get('/chat/tickets', [ChatController::class, 'getTickets'])->name('chat.tickets');
    Route::get('/chat/tickets/{ticketNumber}', [ChatController::class, 'getTicket'])->name('chat.ticket');
    Route::post('/chat/tickets/{ticketNumber}/reopen', [ChatController::class, 'requestReopen'])->name('chat.reopen');
    Route::get('/chat/tickets/{ticketNumber}/new-messages', [ChatController::class, 'getNewMessages'])->name('chat.new-messages');
    Route::post('/chat/tickets/{ticketNumber}/typing/start', [ChatController::class, 'startTyping'])->name('chat.typing.start');
    Route::post('/chat/tickets/{ticketNumber}/typing/stop', [ChatController::class, 'stopTyping'])->name('chat.typing.stop');
    Route::get('/chat/tickets/{ticketNumber}/typing', [ChatController::class, 'getTypingIndicators'])->name('chat.typing');

    // Leave Requests (Employee Only)
    Route::resource('leave-requests', App\Http\Controllers\User\LeaveRequestController::class)->names('user.leave-requests');

    // Friendship Routes
    Route::get('/friends', [App\Http\Controllers\FriendshipController::class, 'index'])->name('friends.index');
    Route::get('/test-friends', function() {
        return view('friends.index', [
            'friends' => collect(),
            'pendingRequests' => collect(),
            'sentRequests' => collect()
        ]);
    })->name('test.friends');
    Route::get('/friends-debug', function() {
        return view('friends.test');
    })->name('friends.debug');
    Route::get('/friends-simple', function() {
        $user = auth()->user();
        if (!$user) {
            return redirect('/login');
        }

        $friends = $user->friends()->get();
        $pendingRequests = $user->pendingFriendRequests()->with('user')->get();
        $sentRequests = $user->sentFriendRequests()->with('friend')->get();

        return view('friends.simple', compact('friends', 'pendingRequests', 'sentRequests'));
    })->name('friends.simple');
    Route::get('/friends/search', [App\Http\Controllers\FriendshipController::class, 'search'])->name('friends.search');
    Route::post('/friends/send-request', [App\Http\Controllers\FriendshipController::class, 'sendRequest'])->name('friends.send-request');
    Route::post('/friends/{friendship}/accept', [App\Http\Controllers\FriendshipController::class, 'acceptRequest'])->name('friends.accept');
    Route::post('/friends/{friendship}/reject', [App\Http\Controllers\FriendshipController::class, 'rejectRequest'])->name('friends.reject');
    Route::delete('/friends/{friend}/remove', [App\Http\Controllers\FriendshipController::class, 'removeFriend'])->name('friends.remove');
    Route::post('/friends/{user}/block', [App\Http\Controllers\FriendshipController::class, 'blockUser'])->name('friends.block');

    // User Chat Routes
    Route::get('/user-chat', [App\Http\Controllers\UserChatController::class, 'index'])->name('user-chat.index');
    Route::get('/user-chat/{friend}/messages', [App\Http\Controllers\UserChatController::class, 'getChat'])->name('user-chat.messages');
    Route::post('/user-chat/send', [App\Http\Controllers\UserChatController::class, 'sendMessage'])->name('user-chat.send');
    Route::get('/user-chat/unread-count', [App\Http\Controllers\UserChatController::class, 'getUnreadCount'])->name('user-chat.unread-count');
    Route::post('/user-chat/mark-read', [App\Http\Controllers\UserChatController::class, 'markAsRead'])->name('user-chat.mark-read');
    Route::get('/user-chat/recent', [App\Http\Controllers\UserChatController::class, 'getRecentChats'])->name('user-chat.recent');

    // Status Routes
    Route::post('/status/update', [StatusController::class, 'updateStatus'])->name('status.update');
    Route::get('/status', [StatusController::class, 'getStatus'])->name('status.get');

    // Profile Routes (Updated with new functionality)
    Route::get('/profile', [App\Http\Controllers\User\ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [App\Http\Controllers\User\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [App\Http\Controllers\User\ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile/picture', [App\Http\Controllers\User\ProfileController::class, 'removeProfilePicture'])->name('profile.picture.remove');
    Route::delete('/profile/cover', [App\Http\Controllers\User\ProfileController::class, 'removeCoverPhoto'])->name('profile.cover.remove');

    // Feedback Routes
    Route::resource('feedback', App\Http\Controllers\User\FeedbackController::class)->names('user.feedback');
    Route::get('feedback-stats', [App\Http\Controllers\User\FeedbackController::class, 'getStats'])->name('user.feedback.stats');

    // Forum Routes
    Route::get('forum', [App\Http\Controllers\User\ForumController::class, 'index'])->name('forum.index');
    Route::get('forum/{forum}', [App\Http\Controllers\User\ForumController::class, 'show'])->name('forum.show');
    Route::post('forum/like', [App\Http\Controllers\User\ForumController::class, 'like'])->name('forum.like');
    Route::post('forum/save', [App\Http\Controllers\User\ForumController::class, 'save'])->name('forum.save');
    Route::post('forum/share', [App\Http\Controllers\User\ForumController::class, 'share'])->name('forum.share');
    Route::post('forum/comment', [App\Http\Controllers\User\ForumController::class, 'comment'])->name('forum.comment');
    Route::post('forum/comment/like', [App\Http\Controllers\User\ForumController::class, 'likeComment'])->name('forum.comment.like');
    Route::get('forum/saved', [App\Http\Controllers\User\ForumController::class, 'saved'])->name('forum.saved');

    // Notification Routes
    Route::get('notifications', [App\Http\Controllers\User\NotificationController::class, 'index'])->name('notifications.index');
    Route::get('notifications/unread-count', [App\Http\Controllers\User\NotificationController::class, 'getUnreadCount'])->name('notifications.unread-count');
    Route::get('notifications/recent', [App\Http\Controllers\User\NotificationController::class, 'getRecent'])->name('notifications.recent');
    Route::post('notifications/mark-read', [App\Http\Controllers\User\NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::post('notifications/mark-unread', [App\Http\Controllers\User\NotificationController::class, 'markAsUnread'])->name('notifications.mark-unread');
    Route::delete('notifications/delete', [App\Http\Controllers\User\NotificationController::class, 'delete'])->name('notifications.delete');
    Route::delete('notifications/clear-all', [App\Http\Controllers\User\NotificationController::class, 'clearAll'])->name('notifications.clear-all');
});


// Authentication routes are handled above with role-based login

