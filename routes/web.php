<?php

use App\Http\Controllers\Admin\AdminPermissionController;
use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\ApiMonitoringController;
use App\Http\Controllers\Admin\BillingController;
use App\Http\Controllers\Admin\ConfessionBannedWordController;
use App\Http\Controllers\Admin\ConfessionController;
use App\Http\Controllers\Admin\ContactMessageController as AdminContactMessageController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\DtrController;
use App\Http\Controllers\Admin\DtrTimeRequestController;
use App\Http\Controllers\Admin\EmployeeDashboardController;
use App\Http\Controllers\Admin\EmployeeRecordsController;
use App\Http\Controllers\Admin\EmployeeDocumentController;
use App\Http\Controllers\Admin\ErrorLogController;
use App\Http\Controllers\Admin\EvaluationController;
use App\Http\Controllers\Admin\FeedbackController;
use App\Http\Controllers\Admin\FileController;
use App\Http\Controllers\Admin\FileRequestController;
use App\Http\Controllers\Admin\ForumController;
use App\Http\Controllers\Admin\HiringPositionController;
use App\Http\Controllers\Admin\HiringProcessController;
use App\Http\Controllers\Admin\HolidayCalendarController;
use App\Http\Controllers\Admin\HrDashboardController;
use App\Http\Controllers\Admin\ImportController as AdminImportController;
use App\Http\Controllers\Admin\KpiController;
use App\Http\Controllers\Admin\LandingPageController;
use App\Http\Controllers\Admin\LeaveRequestController;
use App\Http\Controllers\Admin\LinkedAccountController;
use App\Http\Controllers\Admin\LiveChatController as AdminLiveChatController;
use App\Http\Controllers\Admin\NetworkGraphController;
use App\Http\Controllers\Admin\NewsController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\OmadaController;
use App\Http\Controllers\Admin\PayslipController;
use App\Http\Controllers\Admin\QuizController as AdminQuizController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\StackController;
use App\Http\Controllers\Admin\StarlinkController;
use App\Http\Controllers\Admin\StudentDashboardController;
use App\Http\Controllers\Admin\StudentNdaController;
use App\Http\Controllers\Admin\SubscriptionPlanTypeController;
use App\Http\Controllers\Admin\SystemAnnouncementController;
use App\Http\Controllers\Admin\TaskController;
use App\Http\Controllers\Admin\TeacherExcusedRequestController as AdminTeacherExcusedRequestController;
use App\Http\Controllers\Admin\TeacherMoaController as AdminTeacherMoaController;
use App\Http\Controllers\Admin\TicketProblemTypeController;
use App\Http\Controllers\Admin\TravelTimeLocationController;
use App\Http\Controllers\Admin\TicketReportController;
use App\Http\Controllers\Admin\TimeReportController;
use App\Http\Controllers\Admin\UniversityController;
use App\Http\Controllers\Admin\UserActivityController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\UserMapController;
use App\Http\Controllers\AnonymousChatController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\RoleLoginController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ChatMediaController;
use App\Http\Controllers\DocumentExportVerificationController;
use App\Http\Controllers\FriendshipController;
use App\Http\Controllers\GroupChatController;
use App\Http\Controllers\HiringApplicationController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\QrCodeController;
use App\Http\Controllers\RedirectController;
use App\Http\Controllers\ReportProblemController;
use App\Http\Controllers\SayItComfyUiProxyController;
use App\Http\Controllers\SayItController;
use App\Http\Controllers\SayItSdWebUiProxyController;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\TeacherInviteController;
use App\Http\Controllers\User\AccountTerminatedController;
use App\Http\Controllers\User\DashboardController as UserDashboardController;
use App\Http\Controllers\User\EmployeeFileRequestController;
use App\Http\Controllers\User\FileController as UserFileController;
use App\Http\Controllers\User\ProfileController;
use App\Http\Controllers\User\QuizController as UserQuizController;
use App\Http\Controllers\User\TeacherMoaController as UserTeacherMoaController;
use App\Http\Controllers\User\TechnicianTicketController;
use App\Http\Controllers\UserChatController;
use App\Http\Controllers\UserGeoLocationController;
use App\Http\Controllers\UserStoryController;
use App\Http\Middleware\VerifySayItComfyUiProxySecret;
use App\Http\Middleware\VerifySayItSdWebUiProxySecret;
use App\Models\EmployeeFileRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Proxies (public Base URL + Internal API URL): X-SayIt-Image-Proxy-Secret or legacy X-SayIt-Sd-Proxy-Secret.
Route::middleware(['web', VerifySayItSdWebUiProxySecret::class, 'throttle:60,1'])
    ->any('/sdapi/{path?}', [SayItSdWebUiProxyController::class, 'forward'])
    ->where('path', '.*')
    ->name('sayit.sd.webui.proxy');

Route::middleware(['web', VerifySayItComfyUiProxySecret::class, 'throttle:60,1'])
    ->any('/comfyui/{path?}', [SayItComfyUiProxyController::class, 'forward'])
    ->where('path', '.*')
    ->name('sayit.comfyui.proxy');

// Landing Page Routes
Route::get('/', [LandingController::class, 'index'])->name('landing.index');
Route::get('/projects', [LandingController::class, 'projects'])->name('landing.projects');
Route::get('/news', [LandingController::class, 'news'])->name('landing.news');
Route::get('/about', [LandingController::class, 'about'])->name('landing.about');
Route::get('/contact', [LandingController::class, 'contact'])->name('landing.contact');
Route::post('/contact', [LandingController::class, 'storeContact'])->name('landing.contact.store');
// Report a Problem – public (no login required); anyone can submit a ticket
Route::get('/report-problem', [ReportProblemController::class, 'show'])->name('report-problem');
Route::post('/report-problem', [ReportProblemController::class, 'store'])->name('report-problem.store');
Route::get('/image-proxy/{path}', [LandingController::class, 'imageProxy'])->where('path', '.*')->name('landing.image-proxy');
Route::get('/privacy-policy', [LandingController::class, 'privacyPolicy'])->name('landing.privacy-policy');
Route::get('/tor-pdf', [LandingController::class, 'torPdf'])->name('landing.tor-pdf');

// Say-it: Anonymous confession board (no login, /Say-it only)
Route::get('/Say-it', [SayItController::class, 'index']);
Route::post('/Say-it', [SayItController::class, 'storePost']);
Route::post('/Say-it/generate-image', [SayItController::class, 'generateImage'])
    ->middleware('throttle:say-it-ai-image');
Route::get('/Say-it/composer-mesh-preview', [SayItController::class, 'composerMeshPreview'])
    ->middleware('throttle:60,1');
Route::get('/Say-it/{post}', [SayItController::class, 'show'])->where('post', '[0-9]+');
Route::post('/Say-it/comment', [SayItController::class, 'storeComment']);
Route::post('/Say-it/vote', [SayItController::class, 'vote']);
Route::delete('/Say-it/post/{post}', [SayItController::class, 'destroyPost'])->where('post', '[0-9]+')->name('say-it.post.delete');

// QR Code Scanning Route (Public) - Uses hashed token for one-time access
Route::get('/qr/{token}', [QrCodeController::class, 'scan'])->name('qr.scan');

// Document export verification (Public)
Route::get('/verify/document/{token}', [DocumentExportVerificationController::class, 'show'])
    ->name('document-export.verify');

// Public Hiring Application Routes (dynamic URL based on admin settings)
// The route will be registered dynamically in the controller based on settings
Route::get('/hiring/accept/{token}', [HiringApplicationController::class, 'acceptWithToken'])->name('hiring.accept');
Route::get('/hiring/application/success', [HiringApplicationController::class, 'success'])->name('hiring.application.success');

// Role-based Login Routes
Route::get('/login', [RoleLoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [RoleLoginController::class, 'login']);
Route::post('/logout', [RoleLoginController::class, 'logout'])->name('logout');

// Registration (named route required by landing login and hiring views)
Route::get('/register', [RegisteredUserController::class, 'create'])->name('register')->middleware('guest');
Route::post('/register', [RegisteredUserController::class, 'store'])->middleware('guest');

// Teacher invitation activation (public)
Route::middleware('guest')->group(function () {
    Route::get('/teacher/invite/{token}', [TeacherInviteController::class, 'showActivationForm'])->name('teacher.invites.activate');
    Route::post('/teacher/invite/{token}', [TeacherInviteController::class, 'activate'])->name('teacher.invites.activate.submit');
});

// Include Auth Routes (Password Reset, etc.)
require __DIR__.'/auth.php';

// Redirect authenticated users
Route::get('/home', [RedirectController::class, 'home'])->name('home');

// Admin Routes
Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/hr-dashboard', [HrDashboardController::class, 'index'])->name('admin.hr-dashboard');
    Route::get('/my-permissions', [AdminPermissionController::class, 'myPermissions'])->name('admin.my-permissions');
    Route::get('/activity-data', [DashboardController::class, 'getActivityData'])->name('admin.activity-data');
    Route::redirect('/teacher-invites', '/admin/teachers-management/invite-links');

    /**
     * Teacher invite links — registered here (auth + admin only) so route names always exist for
     * redirects and caches. TeacherInviteController enforces admin.permission:user_management.
     */
    Route::middleware(['admin.permission:user_management', 'admin.subfeature:user_management,teacher_invites'])->group(function () {
        Route::get('teachers-management/invite-links', [TeacherInviteController::class, 'adminIndex'])->name('admin.teacher-invites.index');
        Route::post('teachers-management/invite-links', [TeacherInviteController::class, 'adminStore'])->name('admin.teacher-invites.store');
    });

    // System (parent: system; sub-features gate each area)
    Route::middleware(['admin.permission:system'])->group(function () {
        Route::middleware(['admin.subfeature:system,settings'])->group(function () {
            Route::get('/settings', [SettingsController::class, 'index'])->name('admin.settings.index');
            Route::post('/settings', [SettingsController::class, 'update'])->name('admin.settings.update');
            Route::get('/settings/health', [SettingsController::class, 'getHealth'])->name('admin.settings.health');
            Route::get('/settings/health-metrics', [SettingsController::class, 'getHealthMetrics'])->name('admin.settings.health-metrics');
            Route::post('/settings/test-email', [SettingsController::class, 'testEmail'])->name('admin.settings.test-email');
        });
        Route::middleware(['admin.subfeature:system,rules'])->group(function () {
            Route::get('/system/rules', [SettingsController::class, 'rulesRegulations'])->name('admin.system.rules');
            Route::post('/system/rules', [SettingsController::class, 'updateRulesRegulations'])->name('admin.system.rules.update');
            Route::post('/system/rules/merit-notices', [SettingsController::class, 'updateMeritNoticeSettings'])->name('admin.system.rules.merit-notices.update');
        });
        Route::middleware(['admin.subfeature:system,calendar'])->group(function () {
            Route::get('/system/calendar', [HolidayCalendarController::class, 'index'])->name('admin.system.calendar.index');
            Route::post('/system/calendar', [HolidayCalendarController::class, 'store'])->name('admin.system.calendar.store');
            Route::put('/system/calendar/{dtrHoliday}', [HolidayCalendarController::class, 'update'])->name('admin.system.calendar.update');
            Route::delete('/system/calendar/{dtrHoliday}', [HolidayCalendarController::class, 'destroy'])->name('admin.system.calendar.destroy');
        });
        Route::middleware(['admin.subfeature:system,travel_time'])->group(function () {
            Route::get('/system/travel-time', [TravelTimeLocationController::class, 'index'])->name('admin.system.travel-time.index');
            Route::post('/system/travel-time', [TravelTimeLocationController::class, 'store'])->name('admin.system.travel-time.store');
            Route::get('/system/travel-time/{travelTimeLocation}/edit', [TravelTimeLocationController::class, 'edit'])->name('admin.system.travel-time.edit');
            Route::put('/system/travel-time/{travelTimeLocation}', [TravelTimeLocationController::class, 'update'])->name('admin.system.travel-time.update');
            Route::delete('/system/travel-time/{travelTimeLocation}', [TravelTimeLocationController::class, 'destroy'])->name('admin.system.travel-time.destroy');
        });
        Route::middleware(['admin.subfeature:system,api_monitoring'])->group(function () {
            Route::get('/system/api-monitoring', [ApiMonitoringController::class, 'index'])->name('admin.system.api-monitoring.index');
            Route::get('/system/api-monitoring/metrics', [ApiMonitoringController::class, 'metrics'])->name('admin.system.api-monitoring.metrics');
            Route::post('/system/api-monitoring/external-access', [ApiMonitoringController::class, 'updateExternalAccess'])->name('admin.system.api-monitoring.external-access');
            Route::post('/system/api-monitoring/external-allowed-apis', [ApiMonitoringController::class, 'updateExternalAllowedApis'])->name('admin.system.api-monitoring.external-allowed-apis');
            Route::post('/system/api-monitoring/keys', [ApiMonitoringController::class, 'createApiKey'])->name('admin.system.api-monitoring.keys.store');
            Route::post('/system/api-monitoring/keys/{key}/revoke', [ApiMonitoringController::class, 'revokeApiKey'])->name('admin.system.api-monitoring.keys.revoke');
        });
        Route::middleware(['admin.subfeature:system,network_graph'])->group(function () {
            Route::get('/system/network-graph', [NetworkGraphController::class, 'index'])->name('admin.system.network-graph.index');
            Route::get('/system/network-graph/data', [NetworkGraphController::class, 'graphData'])->name('admin.system.network-graph.data');
            Route::post('/system/network-graph/sync', [NetworkGraphController::class, 'sync'])->name('admin.system.network-graph.sync');
            Route::post('/system/network-graph/clear', [NetworkGraphController::class, 'clear'])->name('admin.system.network-graph.clear');
        });
    });

    // Billing (Subscriptions → Billing sub-feature)
    Route::middleware(['admin.permission:billing', 'admin.subfeature:subscriptions,billing'])->group(function () {
        Route::get('billing', [BillingController::class, 'index'])->name('admin.billing.index');
        Route::post('billing/mark-paid', [BillingController::class, 'markAsPaid'])->name('admin.billing.mark-paid');
        Route::post('billing/advance-payment', [BillingController::class, 'markAdvancePayment'])->name('admin.billing.advance-payment');
        Route::get('billing/statement/{billingStatement}', [BillingController::class, 'showStatement'])->name('admin.billing.statement');
        Route::get('billing/statement/{billingStatement}/pdf', [BillingController::class, 'downloadPdf'])->name('admin.billing.statement.pdf');
    });

    // Admin Permissions Management (System → Admin Permissions sub-feature)
    Route::middleware(['admin.permission:system', 'admin.subfeature:system,admin_permissions'])->group(function () {
        Route::get('admin-permissions', [AdminPermissionController::class, 'index'])->name('admin.admin-permissions.index');
        Route::get('admin-permissions/create', [AdminPermissionController::class, 'create'])->name('admin.admin-permissions.create');
        Route::post('admin-permissions', [AdminPermissionController::class, 'store'])->name('admin.admin-permissions.store');
        Route::get('admin-permissions/{user}/edit', [AdminPermissionController::class, 'edit'])->name('admin.admin-permissions.edit');
        Route::put('admin-permissions/{user}', [AdminPermissionController::class, 'update'])->name('admin.admin-permissions.update');
        Route::delete('admin-permissions/{user}', [AdminPermissionController::class, 'destroy'])->name('admin.admin-permissions.destroy');
    });

    // User Management
    Route::middleware(['admin.permission:user_management'])->group(function () {
        Route::middleware(['admin.subfeature:user_management,users'])->group(function () {
            Route::get('users/api', [AdminUserController::class, 'api'])->name('admin.users.api');
            Route::get('users/export/pdf', [AdminUserController::class, 'exportPdf'])->name('admin.users.export-pdf');
            Route::post('users/import-employee-profile', [AdminUserController::class, 'importEmployeeProfile'])->name('admin.users.import-employee-profile');
            Route::get('users/employee-profile-template', [AdminUserController::class, 'downloadEmployeeProfileTemplate'])->name('admin.users.employee-profile-template');
            Route::resource('users', AdminUserController::class)->names([
                'index' => 'admin.users.index',
                'create' => 'admin.users.create',
                'store' => 'admin.users.store',
                'show' => 'admin.users.show',
                'edit' => 'admin.users.edit',
                'update' => 'admin.users.update',
                'destroy' => 'admin.users.destroy',
            ]);
            Route::patch('users/{user}/toggle-status', [AdminUserController::class, 'toggleStatus'])->name('admin.users.toggle-status');
            Route::patch('users/{user}/approve', [AdminUserController::class, 'approve'])->name('admin.users.approve');
            Route::patch('users/{user}/disapprove', [AdminUserController::class, 'disapprove'])->name('admin.users.disapprove');
            Route::patch('users/{user}/overtime-window', [AdminUserController::class, 'updateOvertimeWindow'])->name('admin.users.overtime-window');
            Route::patch('users/{user}/leave-balance', [AdminUserController::class, 'updateLeaveBalance'])->name('admin.users.leave-balance');
            Route::post('users/bulk-assign-role', [AdminUserController::class, 'bulkAssignRole'])->name('admin.users.bulk-assign-role');
            Route::post('users/bulk-assign-department', [AdminUserController::class, 'bulkAssignDepartment'])->name('admin.users.bulk-assign-department');
            Route::post('users/bulk-assign-ojt-target-end-date', [AdminUserController::class, 'bulkAssignOjtTargetEndDate'])->name('admin.users.bulk-assign-ojt-target-end-date');
            Route::post('users/{user}/send-credentials', [AdminUserController::class, 'sendCredentials'])->name('admin.users.send-credentials');
            Route::post('users/send-bulk-credentials', [AdminUserController::class, 'sendBulkCredentials'])->name('admin.users.send-bulk-credentials');
        });

        Route::middleware(['admin.subfeature:user_management,teachers'])->group(function () {
            Route::get('teachers-management/teachers', [AdminUserController::class, 'teachersManagement'])->name('admin.teachers-management.teachers');
            Route::get('teachers-management/teachers/export/pdf', [AdminUserController::class, 'teachersManagementExportPdf'])->name('admin.teachers-management.export-pdf');
        });
        Route::middleware(['admin.subfeature:user_management,teacher_moa'])->group(function () {
            Route::get('teachers-management/moa', [AdminTeacherMoaController::class, 'index'])->name('admin.teacher-moa.index');
            Route::post('teachers-management/moa/{user}/allow-reupload', [AdminTeacherMoaController::class, 'allowReupload'])->name('admin.teacher-moa.allow-reupload');
            Route::get('teachers-management/moa/{user}/preview', [AdminTeacherMoaController::class, 'preview'])->name('admin.teacher-moa.preview');
        });
        Route::middleware(['admin.subfeature:user_management,teacher_excused'])->group(function () {
            Route::get('teachers-management/teacher-excused-requests', [AdminTeacherExcusedRequestController::class, 'index'])->name('admin.teacher-excused-requests.index');
        });

        Route::middleware(['admin.subfeature:user_management,universities'])->group(function () {
            Route::resource('universities', UniversityController::class)->names([
                'index' => 'admin.universities.index',
                'create' => 'admin.universities.create',
                'store' => 'admin.universities.store',
                'show' => 'admin.universities.show',
                'edit' => 'admin.universities.edit',
                'update' => 'admin.universities.update',
                'destroy' => 'admin.universities.destroy',
            ]);
            Route::patch('universities/{university}/toggle-status', [UniversityController::class, 'toggleStatus'])->name('admin.universities.toggle-status');
        });

        Route::middleware(['admin.subfeature:user_management,departments'])->group(function () {
            Route::resource('departments', DepartmentController::class)->names('admin.departments');
            Route::patch('departments/{department}/toggle-status', [DepartmentController::class, 'toggleStatus'])->name('admin.departments.toggle-status');
        });

        Route::middleware(['admin.subfeature:user_management,user_maps'])->group(function () {
            Route::get('user-maps', [UserMapController::class, 'index'])->name('admin.user-maps.index');
            Route::get('user-maps/data', [UserMapController::class, 'mapData'])->name('admin.user-maps.data');
        });
    });

    // Content Management (sub-features gate sidebar areas; tasks/import remain parent-only)
    Route::middleware(['admin.permission:content_management'])->group(function () {
        Route::middleware(['admin.subfeature:content_management,quizzes'])->group(function () {
            Route::get('quizzes/import/form', [AdminQuizController::class, 'importForm'])->name('admin.quizzes.import-form');
            Route::post('quizzes/import', [AdminQuizController::class, 'import'])->name('admin.quizzes.import');
            Route::get('quizzes/import/template', [AdminQuizController::class, 'downloadTemplate'])->name('admin.quizzes.download-template');
            Route::get('quizzes/template/download', [AdminQuizController::class, 'downloadTemplate'])->name('admin.quizzes.template.download');
            Route::post('quizzes/export-csv', [AdminQuizController::class, 'exportToCsv'])->name('admin.quizzes.export-csv');

            Route::resource('quizzes', AdminQuizController::class);

            Route::get('quizzes/{quiz}/results', [AdminQuizController::class, 'results'])->name('admin.quizzes.results');
            Route::get('quizzes/{quiz}/export-history/pdf', [AdminQuizController::class, 'exportQuizHistoryPdf'])->name('admin.quizzes.export-history-pdf');
            Route::post('quizzes/{quiz}/assign', [AdminQuizController::class, 'assignToUsers'])->name('admin.quizzes.assign');
            Route::get('quizzes/{quiz}/assigned-users', [AdminQuizController::class, 'getAssignedUsers'])->name('admin.quizzes.assigned-users');
            Route::post('quizzes/{quiz}/import-questions', [AdminQuizController::class, 'importQuestions'])->name('admin.quizzes.import-questions');
            Route::post('quiz-assignments/{assignment}/reset', [AdminQuizController::class, 'resetAssignment'])->name('admin.quiz-assignments.reset');
            Route::get('quiz-assignments/{assignment}/history', [AdminQuizController::class, 'viewAttemptHistory'])->name('admin.quiz-assignments.history');
            Route::get('quiz-assignments/{assignment}/history/pdf', [AdminQuizController::class, 'exportAttemptHistoryPdf'])->name('admin.quiz-assignments.history.pdf');
            Route::post('quiz-assignments/{assignment}/allow-retake', [AdminQuizController::class, 'allowRetake'])->name('admin.quiz-assignments.allow-retake');
            Route::get('quiz-attempts/{attemptId}/details', [AdminQuizController::class, 'getAttemptDetails'])->name('admin.quiz-attempts.details');
            Route::get('quizzes/{quizId}/users/{userId}/history', [AdminQuizController::class, 'getUserQuizHistory'])->name('admin.quizzes.user-history');
        });

        Route::middleware(['admin.subfeature:content_management,manual_grading'])->group(function () {
            Route::get('manual-grading', [AdminQuizController::class, 'manualGrading'])->name('admin.manual-grading');
            Route::get('all-text-attempts', [AdminQuizController::class, 'allTextAttempts'])->name('admin.all-text-attempts');
            Route::post('quiz-attempts/{attempt}/grade', [AdminQuizController::class, 'gradeAttempt'])->name('admin.quiz-attempts.grade');
        });

        Route::middleware(['admin.subfeature:content_management,news'])->group(function () {
            // News Management
            Route::resource('news', NewsController::class)->names([
                'index' => 'admin.news.index',
                'create' => 'admin.news.create',
                'store' => 'admin.news.store',
                'show' => 'admin.news.show',
                'edit' => 'admin.news.edit',
                'update' => 'admin.news.update',
                'destroy' => 'admin.news.destroy',
            ]);
            Route::post('news/{news}/toggle-publish', [NewsController::class, 'togglePublish'])->name('admin.news.toggle-publish');
            Route::post('news/toggle-section', [NewsController::class, 'toggleNewsSection'])->name('admin.news.toggle-section');
        });

        Route::middleware(['admin.subfeature:content_management,forum'])->group(function () {
            Route::resource('forum', ForumController::class)->names('admin.forum');
            Route::patch('forum/{forum}/toggle-publish', [ForumController::class, 'togglePublish'])->name('admin.forum.toggle-publish');
            Route::patch('forum/{forum}/toggle-pin', [ForumController::class, 'togglePin'])->name('admin.forum.toggle-pin');
            Route::post('forum/comment', [ForumController::class, 'comment'])->name('admin.forum.comment');
            Route::post('forum/comment/like', [ForumController::class, 'likeComment'])->name('admin.forum.comment.like');
        });

        Route::middleware(['admin.subfeature:content_management,evaluations'])->group(function () {
            Route::resource('evaluations', EvaluationController::class)->except(['show'])->names('admin.evaluations');
            Route::post('evaluations/{evaluation}/activate', [EvaluationController::class, 'activate'])->name('admin.evaluations.activate');
            Route::get('evaluations/{evaluation}/submissions', [EvaluationController::class, 'submissions'])->name('admin.evaluations.submissions');
            Route::post('evaluations/force-send', [EvaluationController::class, 'forceSend'])->name('admin.evaluations.force-send');
        });

        Route::middleware(['admin.subfeature:content_management,announcements'])->group(function () {
            Route::resource('system-announcements', SystemAnnouncementController::class)->except(['show'])->names('admin.system-announcements');
            Route::post('system-announcements/{system_announcement}/publish', [SystemAnnouncementController::class, 'publish'])->name('admin.system-announcements.publish');
            Route::post('system-announcements/{system_announcement}/unpublish', [SystemAnnouncementController::class, 'unpublish'])->name('admin.system-announcements.unpublish');
        });

        Route::get('import', [AdminImportController::class, 'index'])->name('admin.import');
        Route::post('import', [AdminImportController::class, 'import'])->name('admin.import.process');
        Route::get('import/template', [AdminImportController::class, 'downloadTemplate'])->name('admin.import.template');
    });

    Route::middleware(['admin.permission:tasks'])->group(function () {
        Route::middleware(['admin.subfeature:task_management,task_dashboard'])->group(function () {
            Route::get('tasks/dashboard', [TaskController::class, 'dashboard'])->name('admin.tasks.dashboard');
            Route::get('tasks/dashboard/chart-data', [TaskController::class, 'getChartData'])->name('admin.tasks.dashboard.chart-data');
        });

        Route::get('tasks', [TaskController::class, 'index'])->name('admin.tasks.index');
        Route::post('tasks', [TaskController::class, 'store'])->name('admin.tasks.store');
        Route::put('tasks/{task}', [TaskController::class, 'update'])->name('admin.tasks.update');
        Route::post('tasks/{task}/reorder', [TaskController::class, 'reorder'])->name('admin.tasks.reorder');
        Route::delete('tasks/{task}', [TaskController::class, 'destroy'])->name('admin.tasks.destroy');
        Route::post('tasks/update-order', [TaskController::class, 'updateOrder'])->name('admin.tasks.update-order');
        Route::post('tasks/{task}/comments', [TaskController::class, 'addComment'])->name('admin.tasks.add-comment');
        Route::post('tasks/{task}/attachments', [TaskController::class, 'uploadAttachment'])->name('admin.tasks.upload-attachment');
        Route::delete('tasks/attachments/{attachment}', [TaskController::class, 'deleteAttachment'])->name('admin.tasks.delete-attachment');
        Route::post('tasks/{task}/assign-users', [TaskController::class, 'assignUsers'])->name('admin.tasks.assign-users');
        Route::post('tasks/{task}/generate-invite-code', [TaskController::class, 'generateInviteCode'])->name('admin.tasks.generate-invite-code');
        Route::post('tasks/{task}/get-invite-link', [TaskController::class, 'getInviteLink'])->name('admin.tasks.get-invite-link');
        Route::post('tasks/{task}/generate-share-link', [TaskController::class, 'generateShareLink'])->name('admin.tasks.generate-share-link');
        Route::post('tasks/{task}/invite-users', [TaskController::class, 'inviteUsers'])->name('admin.tasks.invite-users');
        Route::post('tasks/join-by-code', [TaskController::class, 'joinByCode'])->name('admin.tasks.join-by-code')->middleware('auth');
        Route::get('tasks/join-by-link/{token}', [TaskController::class, 'joinByLink'])->name('admin.tasks.join-by-link')->middleware('auth');
        Route::post('tasks/invitations/{invitation}/accept', [TaskController::class, 'acceptInvitation'])->name('admin.tasks.invitations.accept');
        Route::post('tasks/invitations/{invitation}/reject', [TaskController::class, 'rejectInvitation'])->name('admin.tasks.invitations.reject');
        Route::get('tasks/pending-invitations', [TaskController::class, 'getPendingInvitations'])->name('admin.tasks.pending-invitations');
        Route::post('tasks/{task}/convert-to-group', [TaskController::class, 'convertToGroup'])->name('admin.tasks.convert-to-group');
        Route::post('tasks/custom-boards', [TaskController::class, 'storeCustomBoard'])->name('admin.tasks.custom-boards.store');
        Route::put('tasks/custom-boards/{customBoard}', [TaskController::class, 'updateCustomBoard'])->name('admin.tasks.custom-boards.update');
        Route::delete('tasks/custom-boards/{customBoard}', [TaskController::class, 'destroyCustomBoard'])->name('admin.tasks.custom-boards.destroy');
        Route::post('tasks/custom-boards/update-order', [TaskController::class, 'updateCustomBoardOrder'])->name('admin.tasks.custom-boards.update-order');
        Route::post('tasks/custom-boards/{customBoard}/toggle-lock', [TaskController::class, 'toggleCustomBoardLock'])->name('admin.tasks.custom-boards.toggle-lock');
        Route::post('tasks/task-lists', [TaskController::class, 'storeTaskList'])->name('admin.tasks.task-lists.store');
        Route::put('tasks/task-lists/{taskList}', [TaskController::class, 'updateTaskList'])->name('admin.tasks.task-lists.update');
        Route::delete('tasks/task-lists/{taskList}', [TaskController::class, 'destroyTaskList'])->name('admin.tasks.task-lists.destroy');
        Route::post('tasks/task-lists/{taskList}/generate-invite-code', [TaskController::class, 'generateTaskListInviteCode'])->name('admin.tasks.task-lists.generate-invite-code');
        Route::post('tasks/task-lists/{taskList}/generate-share-link', [TaskController::class, 'generateTaskListShareLink'])->name('admin.tasks.task-lists.generate-share-link');
        Route::post('tasks/task-lists/{taskList}/send-invitation-email', [TaskController::class, 'sendTaskListInvitationEmail'])->name('admin.tasks.task-lists.send-invitation-email');
        Route::post('tasks/task-lists/join-by-code', [TaskController::class, 'joinTaskListByCode'])->name('admin.tasks.task-lists.join-by-code')->middleware('auth');
        Route::get('tasks/task-lists/join-by-link/{token}', [TaskController::class, 'joinTaskListByLink'])->name('admin.tasks.join-task-list-by-link')->middleware('auth');
        Route::post('tasks/custom-priorities', [TaskController::class, 'storeCustomPriority'])->name('admin.tasks.custom-priorities.store');
        Route::put('tasks/custom-priorities/{customPriority}', [TaskController::class, 'updateCustomPriority'])->name('admin.tasks.custom-priorities.update');
        Route::delete('tasks/custom-priorities/{customPriority}', [TaskController::class, 'destroyCustomPriority'])->name('admin.tasks.custom-priorities.destroy');
    });

    // Analytics & Reports (parent: analytics_reports; sub-areas: admin.analytics:{feature})
    Route::middleware(['admin.permission:analytics_reports'])->group(function () {
        Route::middleware(['admin.analytics:analytics'])->group(function () {
            Route::get('analytics', [AnalyticsController::class, 'index'])->name('admin.analytics.index');
            Route::get('analytics/quiz/{quizId}', [AnalyticsController::class, 'getQuizDetails'])->name('admin.analytics.quiz-details');
            Route::get('analytics/student/{userId}', [AnalyticsController::class, 'getStudentDetails'])->name('admin.analytics.student-details');
            Route::get('analytics/topic/{topic}', [AnalyticsController::class, 'getTopicDetails'])->name('admin.analytics.topic-details')->where('topic', '.*');
        });
        Route::middleware(['admin.analytics:error_logs'])->group(function () {
            Route::get('analytics/error-logs', [ErrorLogController::class, 'index'])->name('admin.analytics.error-logs');
        });
        Route::middleware(['admin.analytics:students_review'])->group(function () {
            Route::get('analytics/students-review', [EvaluationController::class, 'reviews'])->name('admin.evaluations.reviews');
        });
        Route::middleware(['admin.analytics:employee_records'])->group(function () {
            Route::get('employee-records', [EmployeeRecordsController::class, 'index'])->name('admin.employee-records.index');
            Route::get('employee-records/{employee}', [EmployeeRecordsController::class, 'show'])->name('admin.employee-records.show');
        });
    });

    Route::middleware(['admin.analytics:user_activity'])->group(function () {
        Route::get('user-activity', [UserActivityController::class, 'index'])->name('admin.user-activity.index');
        Route::get('user-activity/sessions', [UserActivityController::class, 'sessions'])->name('admin.user-activity.sessions');
        Route::get('user-activity/statistics', [UserActivityController::class, 'statistics'])->name('admin.user-activity.statistics');
        Route::post('user-activity/cleanup', [UserActivityController::class, 'cleanup'])->name('admin.user-activity.cleanup');
    });

    Route::middleware(['admin.analytics:anonymous_chats'])->group(function () {
        Route::get('anonymous-chats', [App\Http\Controllers\Admin\AnonymousChatController::class, 'index'])->name('admin.anonymous-chats.index');
        Route::get('anonymous-chats/{anonymousChatRoom}', [App\Http\Controllers\Admin\AnonymousChatController::class, 'show'])->name('admin.anonymous-chats.show');
    });

    // Employee Management
    Route::middleware(['admin.permission:employee_management'])->group(function () {
        Route::middleware(['admin.subfeature:employee_management,employee_dashboard'])->group(function () {
            Route::get('/employee-dashboard', [EmployeeDashboardController::class, 'index'])->name('admin.employee-dashboard.index');
        });

        Route::middleware(['admin.subfeature:employee_management,kpi_dashboard'])->group(function () {
            Route::get('/kpi/dashboard', [KpiController::class, 'dashboard'])->name('admin.kpi.dashboard');
        });

        Route::middleware(['admin.subfeature:employee_management,file_request'])->group(function () {
            Route::redirect('/file-request/templates', '/admin/file-request');
            Route::redirect('/file-request/templates/{path}', '/admin/file-request')->where('path', '.*');
            Route::post('/file-request/preview', fn () => redirect()
                ->route('admin.file-request.index')
                ->with('error', 'Document generation was removed. Upload a file and use Send to employee.'));
            Route::post('/file-request/generate', fn () => redirect()
                ->route('admin.file-request.index')
                ->with('error', 'Document generation was removed. Upload a file and use Send to employee.'));
            Route::get('/file-request', [FileRequestController::class, 'index'])->name('admin.file-request.index');
            Route::post('/file-request', [FileRequestController::class, 'store'])->name('admin.file-request.store');
            Route::post('/file-request/history/{fileRequest}/fulfill', [FileRequestController::class, 'fulfill'])->name('admin.file-request.fulfill');
            Route::post('/file-request/history/{fileRequest}/reject', [FileRequestController::class, 'reject'])->name('admin.file-request.reject');
            Route::get('/file-request/history/{fileRequest}', function (EmployeeFileRequest $fileRequest) {
                return redirect()->route('admin.file-request.view', $fileRequest);
            })->name('admin.file-request.show');
            Route::get('/file-request/history/{fileRequest}/view', [FileRequestController::class, 'view'])->name('admin.file-request.view');
            Route::get('/file-request/history/{fileRequest}/download', [FileRequestController::class, 'download'])->name('admin.file-request.download');
            Route::delete('/file-request/history/{fileRequest}', [FileRequestController::class, 'destroy'])->name('admin.file-request.destroy');
        });

        Route::middleware(['admin.subfeature:employee_management,payslip'])->group(function () {
            Route::get('/payslip', [PayslipController::class, 'index'])->name('admin.payslip.index');
            Route::post('/payslip/import', [PayslipController::class, 'import'])->name('admin.payslip.import');
            Route::get('/payslip/template', [PayslipController::class, 'downloadTemplate'])->name('admin.payslip.template');
            Route::get('/payslip/yearly-summary', [PayslipController::class, 'yearlySummary'])->name('admin.payslip.yearly-summary');
            Route::get('/payslip/yearly-summary/csv', [PayslipController::class, 'yearlySummaryCsv'])->name('admin.payslip.yearly-summary.csv');
            Route::get('/payslip/cutoff-summary', [PayslipController::class, 'cutoffSummary'])->name('admin.payslip.cutoff-summary');
            Route::get('/payslip/cutoff-summary/csv', [PayslipController::class, 'cutoffSummaryCsv'])->name('admin.payslip.cutoff-summary.csv');
            Route::get('/payslip/{payslip}', [PayslipController::class, 'show'])->name('admin.payslip.show');
            Route::get('/payslip/{payslip}/signed', [PayslipController::class, 'signedPdf'])->name('admin.payslip.signed');
            Route::patch('/payslip/{payslip}/link', [PayslipController::class, 'link'])->name('admin.payslip.link');
            Route::delete('/payslip/{payslip}', [PayslipController::class, 'destroy'])->name('admin.payslip.destroy');
            Route::post('/payslip/bulk-delete', [PayslipController::class, 'bulkDestroy'])->name('admin.payslip.bulk-destroy');
            Route::post('/payslip/bulk-print', [PayslipController::class, 'bulkPrint'])->name('admin.payslip.bulk-print');
        });

        Route::middleware(['admin.subfeature:employee_management,employee_nda'])->get('/employee-documents/nda', fn (EmployeeDocumentController $controller, Request $request) => $controller->index($request, 'nda'))->name('admin.employee-documents.nda');
        Route::middleware(['admin.subfeature:employee_management,employee_contract'])->get('/employee-documents/contract', fn (EmployeeDocumentController $controller, Request $request) => $controller->index($request, 'contract'))->name('admin.employee-documents.contract');
        Route::middleware(['admin.subfeature:employee_management,employee_policy'])->get('/employee-documents/policy', fn (EmployeeDocumentController $controller, Request $request) => $controller->index($request, 'policy'))->name('admin.employee-documents.policy');
        Route::middleware(['admin.subfeature:employee_management,employee_handbook'])->get('/employee-documents/handbook', fn (EmployeeDocumentController $controller, Request $request) => $controller->index($request, 'handbook'))->name('admin.employee-documents.handbook');
        Route::get('/employee-documents/{type}/template', [EmployeeDocumentController::class, 'editTemplate'])->name('admin.employee-documents.template')->where('type', 'nda|contract|policy|handbook');
        Route::post('/employee-documents/{type}/template', [EmployeeDocumentController::class, 'updateTemplate'])->name('admin.employee-documents.template.update')->where('type', 'nda|contract|policy|handbook');
        Route::middleware(['admin.subfeature:employee_management,employee_signatures'])->group(function () {
            Route::get('/employee-documents/signatures', [EmployeeDocumentController::class, 'signatures'])->name('admin.employee-documents.signatures');
            Route::post('/employee-documents/signatures/employees/{employee}/e-signature', [EmployeeDocumentController::class, 'uploadEmployeeESignature'])->name('admin.employee-documents.signatures.upload');
            Route::delete('/employee-documents/signatures/employees/{employee}/e-signature', [EmployeeDocumentController::class, 'removeEmployeeESignature'])->name('admin.employee-documents.signatures.remove');
            Route::get('/employee-documents/signatures/{signature}/preview', [EmployeeDocumentController::class, 'preview'])->name('admin.employee-documents.preview');
        });
        Route::get('/employee-documents/{type}/employees/{employee}/preview', [EmployeeDocumentController::class, 'previewEmployee'])->name('admin.employee-documents.employee-preview')->where('type', 'nda|contract|policy|handbook');

        Route::middleware(['admin.subfeature:employee_management,dtr'])->group(function () {
            // DTR Management (Employees)
            Route::get('/dtr', [DtrController::class, 'index'])->name('admin.dtr.index');
            Route::get('/dtr/create', [DtrController::class, 'create'])->name('admin.dtr.create');
            Route::post('/dtr', [DtrController::class, 'store'])->name('admin.dtr.store');
            Route::get('/dtr/{dtr}/edit', [DtrController::class, 'edit'])->name('admin.dtr.edit');
            Route::put('/dtr/{dtr}', [DtrController::class, 'update'])->name('admin.dtr.update');
            Route::delete('/dtr/{dtr}', [DtrController::class, 'destroy'])->name('admin.dtr.destroy');
            Route::post('/dtr/recalculate-deficits', [DtrController::class, 'recalculateDeficits'])->name('admin.dtr.recalculate-deficits');
            Route::post('/dtr/import', [DtrController::class, 'import'])->name('admin.dtr.import');
            Route::get('/dtr/template', [DtrController::class, 'downloadTemplate'])->name('admin.dtr.template');
            Route::get('/dtr/export-pdf', [DtrController::class, 'exportPdf'])->name('admin.dtr.export-pdf');
        });

        Route::middleware(['admin.subfeature:employee_management,time_report'])->group(function () {
            Route::get('/time-report', [TimeReportController::class, 'index'])->name('admin.time-report.index');
        });

        Route::middleware(['admin.subfeature:employee_management,leave_requests'])->group(function () {
            // Leave Requests Management (Employees)
            Route::get('/leave-requests', [LeaveRequestController::class, 'index'])->name('admin.leave-requests.index');
            Route::get('/leave-requests/export/csv', [LeaveRequestController::class, 'exportApprovedCsv'])->name('admin.leave-requests.export-csv');
            Route::get('/leave-requests/export/pdf', [LeaveRequestController::class, 'exportApprovedPdf'])->name('admin.leave-requests.export-pdf');
            Route::post('/leave-requests/create-for-employee', [LeaveRequestController::class, 'storeForEmployee'])->name('admin.leave-requests.store-for-employee');
            Route::get('/leave-requests/{leaveRequest}/pdf', [LeaveRequestController::class, 'showPdf'])->name('admin.leave-requests.show-pdf');
            Route::get('/leave-requests/{leaveRequest}', [LeaveRequestController::class, 'show'])->name('admin.leave-requests.show');
            Route::patch('/leave-requests/{leaveRequest}/type', [LeaveRequestController::class, 'updateType'])->name('admin.leave-requests.update-type');
            Route::patch('/leave-requests/{leaveRequest}/dates', [LeaveRequestController::class, 'updateDates'])->name('admin.leave-requests.update-dates');
            Route::post('/leave-requests/{leaveRequest}/approve', [LeaveRequestController::class, 'approve'])->name('admin.leave-requests.approve');
            Route::post('/leave-requests/{leaveRequest}/verify', [LeaveRequestController::class, 'verify'])->name('admin.leave-requests.verify');
            Route::post('/leave-requests/{leaveRequest}/force-accept', [LeaveRequestController::class, 'forceAccept'])->name('admin.leave-requests.force-accept');
            Route::post('/leave-requests/{leaveRequest}/reject', [LeaveRequestController::class, 'reject'])->name('admin.leave-requests.reject');
            Route::post('/leave-requests/{leaveRequest}/resubmit', [LeaveRequestController::class, 'resubmit'])->name('admin.leave-requests.resubmit');
            Route::delete('/leave-requests/{leaveRequest}', [LeaveRequestController::class, 'destroy'])->name('admin.leave-requests.destroy');
        });

        Route::middleware(['admin.subfeature:employee_management,leave_calendar'])->group(function () {
            Route::get('/leave-calendar', [LeaveRequestController::class, 'calendar'])->name('admin.leave-requests.calendar');
        });
    });

    // Student Management
    Route::middleware(['admin.permission:student_management'])->group(function () {
        // Students List
        Route::get('/student-management/students', [StudentDashboardController::class, 'students'])->name('admin.student-management.students');
        Route::get('/student-management/students/{user}/merits', [StudentDashboardController::class, 'studentMeritDetails'])->name('admin.student-management.students.merits');
        Route::patch('/student-management/students/{user}/merits', [StudentDashboardController::class, 'updateStudentMeritDetails'])->name('admin.student-management.students.merits.update');

        // Student Management Dashboard
        Route::get('/student-management/dashboard', [StudentDashboardController::class, 'index'])->name('admin.student-management.dashboard');

        // Student DTR Management
        Route::get('/student-dtr', [DtrController::class, 'studentIndex'])->name('admin.student-dtr.index');
        Route::get('/student-dtr/create', [DtrController::class, 'studentCreate'])->name('admin.student-dtr.create');
        Route::post('/student-dtr', [DtrController::class, 'studentStore'])->name('admin.student-dtr.store');
        Route::get('/student-dtr/{dtr}/edit', [DtrController::class, 'studentEdit'])->name('admin.student-dtr.edit');
        Route::put('/student-dtr/{dtr}', [DtrController::class, 'studentUpdate'])->name('admin.student-dtr.update');
        Route::delete('/student-dtr/{dtr}', [DtrController::class, 'studentDestroy'])->name('admin.student-dtr.destroy');
        Route::post('/student-dtr/bulk-update', [DtrController::class, 'studentBulkUpdate'])->name('admin.student-dtr.bulk-update');
        Route::post('/student-dtr/bulk-delete', [DtrController::class, 'studentBulkDelete'])->name('admin.student-dtr.bulk-delete');
        Route::get('/student-dtr/export/pdf', [DtrController::class, 'studentExportPdf'])->name('admin.student-dtr.export-pdf');

        // Student Leave Requests Management
        Route::get('/student-leave-requests', [LeaveRequestController::class, 'studentIndex'])->name('admin.student-leave-requests.index');
        Route::get('/student-leave-calendar', [LeaveRequestController::class, 'studentCalendar'])->name('admin.student-leave-requests.calendar');
        Route::get('/student-management/nda-files', [StudentNdaController::class, 'index'])->name('admin.student-nda-files.index');
        Route::post('/student-management/nda-files/{studentNda}/approve', [StudentNdaController::class, 'approve'])->name('admin.student-nda-files.approve');
        Route::post('/student-management/nda-files/{studentNda}/reject', [StudentNdaController::class, 'reject'])->name('admin.student-nda-files.reject');
        Route::post('/student-management/nda-files/{studentNda}/allow-reupload', [StudentNdaController::class, 'allowReupload'])->name('admin.student-nda-files.allow-reupload');
        Route::get('/student-management/nda-files/{studentNda}/preview', [StudentNdaController::class, 'preview'])->name('admin.student-nda-files.preview');
        Route::post('/student-leave-requests/create-for-student', [LeaveRequestController::class, 'storeForStudent'])->name('admin.student-leave-requests.store-for-student');

        // Student Time Requests Management
        Route::get('/time-requests', [DtrTimeRequestController::class, 'index'])->name('admin.time-requests.index');
        Route::put('/time-requests/{dtrTimeRequest}', [DtrTimeRequestController::class, 'update'])->name('admin.time-requests.update');
        Route::post('/time-requests/{dtrTimeRequest}/approve', [DtrTimeRequestController::class, 'approve'])->name('admin.time-requests.approve');
        Route::post('/time-requests/{dtrTimeRequest}/reject', [DtrTimeRequestController::class, 'reject'])->name('admin.time-requests.reject');
        Route::delete('/time-requests/{dtrTimeRequest}', [DtrTimeRequestController::class, 'destroy'])->name('admin.time-requests.destroy');
    });

    // Hiring Process Management
    Route::middleware(['admin.permission:hiring_process'])->group(function () {
        Route::get('/hiring-process', [HiringProcessController::class, 'index'])->name('admin.hiring-process.index');
        Route::get('/hiring-process/applicants', [HiringProcessController::class, 'applicants'])->name('admin.hiring-process.applicants');

        // Hiring Positions Management
        Route::resource('hiring-positions', HiringPositionController::class)->names('admin.hiring-positions');
        Route::patch('hiring-positions/{hiringPosition}/toggle-status', [HiringPositionController::class, 'toggleStatus'])->name('admin.hiring-positions.toggle-status');

        // Hiring Applications Management
        Route::get('/hiring-applications', [App\Http\Controllers\Admin\HiringApplicationController::class, 'index'])->name('admin.hiring-applications.index');
        Route::get('/hiring-applications/calendar', [App\Http\Controllers\Admin\HiringApplicationController::class, 'calendar'])->name('admin.hiring-applications.calendar');
        Route::get('/hiring-applications/{application}/quiz-assignments/{assignment}/attempts', [App\Http\Controllers\Admin\HiringApplicationController::class, 'showInternQuizAssignmentAttempts'])->name('admin.hiring-applications.intern-quiz-attempts');
        Route::get('/hiring-applications/{application}', [App\Http\Controllers\Admin\HiringApplicationController::class, 'show'])->name('admin.hiring-applications.show');
        Route::post('/hiring-applications/{application}/accept', [App\Http\Controllers\Admin\HiringApplicationController::class, 'accept'])->name('admin.hiring-applications.accept');
        Route::post('/hiring-applications/{application}/reject', [App\Http\Controllers\Admin\HiringApplicationController::class, 'reject'])->name('admin.hiring-applications.reject');
        Route::post('/hiring-applications/{application}/reconsider', [App\Http\Controllers\Admin\HiringApplicationController::class, 'reconsider'])->name('admin.hiring-applications.reconsider');
        Route::post('/hiring-applications/{application}/schedule-interview', [App\Http\Controllers\Admin\HiringApplicationController::class, 'scheduleInterview'])->name('admin.hiring-applications.schedule-interview');
        Route::post('/hiring-applications/{application}/mark-interview-done', [App\Http\Controllers\Admin\HiringApplicationController::class, 'markInterviewDone'])->name('admin.hiring-applications.mark-interview-done');
        Route::post('/hiring-applications/{application}/send-follow-up', [App\Http\Controllers\Admin\HiringApplicationController::class, 'sendFollowUpEmail'])->name('admin.hiring-applications.send-follow-up');
        Route::post('/hiring-applications/{application}/mark-hired', [App\Http\Controllers\Admin\HiringApplicationController::class, 'markAsHired'])->name('admin.hiring-applications.mark-hired');
        Route::post('/hiring-applications/{application}/accept-intern', [App\Http\Controllers\Admin\HiringApplicationController::class, 'acceptIntern'])->name('admin.hiring-applications.accept-intern');
        Route::post('/hiring-applications/{application}/assign-intern-quiz', [App\Http\Controllers\Admin\HiringApplicationController::class, 'assignInternQuiz'])->name('admin.hiring-applications.assign-intern-quiz');
        Route::post('/hiring-applications/{application}/resend-intern-quiz-email', [App\Http\Controllers\Admin\HiringApplicationController::class, 'resendInternQuizEmail'])->name('admin.hiring-applications.resend-intern-quiz-email');
        Route::post('/hiring-applications/{application}/cancel-hired', [App\Http\Controllers\Admin\HiringApplicationController::class, 'cancelHired'])->name('admin.hiring-applications.cancel-hired');
        Route::get('/hiring-applications/{application}/download-resume', [App\Http\Controllers\Admin\HiringApplicationController::class, 'downloadResume'])->name('admin.hiring-applications.download-resume');
        Route::get('/hiring-applications/{application}/view-resume', [App\Http\Controllers\Admin\HiringApplicationController::class, 'viewResume'])->name('admin.hiring-applications.view-resume');
        Route::patch('/hiring-applications/{application}/admin-notes', [App\Http\Controllers\Admin\HiringApplicationController::class, 'updateAdminNotes'])->name('admin.hiring-applications.update-admin-notes');
        Route::delete('/hiring-applications/{application}', [App\Http\Controllers\Admin\HiringApplicationController::class, 'destroy'])->name('admin.hiring-applications.destroy');
    });

    // File Storage (Admin) – requires files permission
    Route::middleware(['admin.permission:files'])->group(function () {
        Route::get('files', [FileController::class, 'index'])->name('admin.files.index');
        Route::post('files', [FileController::class, 'store'])->name('admin.files.store');
        Route::post('files/presign', [FileController::class, 'presignUpload'])->name('admin.files.presign');
        Route::post('files/confirm', [FileController::class, 'confirmUpload'])->name('admin.files.confirm');
        Route::post('files/multipart/initiate', [FileController::class, 'initiateMultipartUpload'])->name('admin.files.multipart.initiate');
        Route::post('files/multipart/presign-chunk', [FileController::class, 'presignChunk'])->name('admin.files.multipart.presign-chunk');
        Route::post('files/multipart/upload-chunk', [FileController::class, 'uploadChunk'])->name('admin.files.multipart.upload-chunk');
        Route::post('files/multipart/complete', [FileController::class, 'completeMultipartUpload'])->name('admin.files.multipart.complete');
        Route::post('files/multipart/abort', [FileController::class, 'abortMultipartUpload'])->name('admin.files.multipart.abort');
        Route::post('files/create-folder', [FileController::class, 'createFolder'])->name('admin.files.create-folder');
        Route::put('files/{file}', [FileController::class, 'update'])->name('admin.files.update');
        Route::delete('files/{file}', [FileController::class, 'destroy'])->name('admin.files.destroy');
        Route::get('files/{file}/download', [FileController::class, 'download'])->name('admin.files.download');
        Route::get('files/{file}/view', [FileController::class, 'view'])->name('admin.files.view');
        Route::post('files/{file}/share', [FileController::class, 'share'])->name('admin.files.share');
        Route::post('files/{file}/unshare', [FileController::class, 'unshare'])->name('admin.files.unshare');
        Route::get('files/{file}/shared-users', [FileController::class, 'getSharedUsers'])->name('admin.files.shared-users');
    });

    // Confession (Say-it) – requires confession permission
    Route::middleware(['admin.permission:confession'])->group(function () {
        Route::get('confession', [ConfessionController::class, 'index']);
        Route::get('confession/dashboard', [ConfessionController::class, 'dashboard']);
        Route::get('confession/topics', [ConfessionController::class, 'topics'])->name('admin.confession.topics');
        Route::get('confession/topics/{confession_topic}/edit', [ConfessionController::class, 'editTopic'])->name('admin.confession.topics.edit');
        Route::put('confession/topics/{confession_topic}', [ConfessionController::class, 'updateTopic'])->name('admin.confession.topics.update');
        Route::delete('confession/topics/{confession_topic}', [ConfessionController::class, 'destroyTopic'])->name('admin.confession.topics.destroy');
        Route::delete('confession/posts/{confession_post}', [ConfessionController::class, 'destroy']);
        Route::post('confession/anon-name-settings', [ConfessionController::class, 'updateAnonNameSettings'])->name('admin.confession.anon-name-settings');
        Route::get('confession/banned-words', [ConfessionBannedWordController::class, 'index'])->name('admin.confession.banned-words');
        Route::post('confession/banned-words', [ConfessionBannedWordController::class, 'store']);
        Route::delete('confession/banned-words/{banned_word}', [ConfessionBannedWordController::class, 'destroy'])->name('admin.confession.banned-words.destroy');
    });

    // Communication
    Route::middleware(['admin.permission:communication'])->group(function () {

        // Notification Management
        Route::get('notifications/unread', [NotificationController::class, 'getUnread'])->name('admin.notifications.unread');
        Route::get('notifications/recent', [NotificationController::class, 'getRecent'])->name('admin.notifications.recent');
        Route::get('notifications/unread-count', [NotificationController::class, 'getUnreadCount'])->name('admin.notifications.unread-count');
        Route::post('notifications/mark-read', [NotificationController::class, 'markAsRead'])->name('admin.notifications.mark-read');
        Route::post('notifications/send-to-all', [NotificationController::class, 'sendToAll'])->name('admin.notifications.send-to-all');
        Route::post('notifications/bulk-delete', [NotificationController::class, 'bulkDelete'])->name('admin.notifications.bulk-delete');
        Route::post('notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('admin.notifications.mark-all-read');
        Route::get('notifications/stats', [NotificationController::class, 'getStats'])->name('admin.notifications.stats');
        Route::resource('notifications', NotificationController::class)->names('admin.notifications');

        // Contact Messages Management
        Route::resource('contact-messages', AdminContactMessageController::class)->only(['index', 'show', 'destroy']);
        Route::post('contact-messages/{contactMessage}/reply', [AdminContactMessageController::class, 'reply'])->name('contact-messages.reply');
        Route::patch('contact-messages/{contactMessage}/close', [AdminContactMessageController::class, 'close'])->name('contact-messages.close');

        // Ticket Reports (problem reports from /report-problem)
        Route::get('tickets', [TicketReportController::class, 'dashboard'])->name('admin.tickets.dashboard');
        Route::get('tickets/open', [TicketReportController::class, 'open'])->name('admin.tickets.open');
        Route::get('tickets/closed', [TicketReportController::class, 'closed'])->name('admin.tickets.closed');
        Route::get('tickets/problem-types', [TicketProblemTypeController::class, 'index'])->name('admin.tickets.problem-types.index');
        Route::post('tickets/problem-types', [TicketProblemTypeController::class, 'store'])->name('admin.tickets.problem-types.store');
        Route::get('tickets/problem-types/{ticket_problem_type}/edit', [TicketProblemTypeController::class, 'edit'])->name('admin.tickets.problem-types.edit');
        Route::put('tickets/problem-types/{ticket_problem_type}', [TicketProblemTypeController::class, 'update'])->name('admin.tickets.problem-types.update');
        Route::delete('tickets/problem-types/{ticket_problem_type}', [TicketProblemTypeController::class, 'destroy'])->name('admin.tickets.problem-types.destroy');
        Route::get('tickets/{ticket_report}', [TicketReportController::class, 'show'])->name('admin.tickets.show');
        Route::patch('tickets/{ticket_report}', [TicketReportController::class, 'update'])->name('admin.tickets.update');
        Route::post('tickets/{ticket_report}/notes', [TicketReportController::class, 'storeNote'])->name('admin.tickets.notes.store');
        Route::delete('tickets/{ticket_report}/notes/{note}', [TicketReportController::class, 'destroyNote'])->name('admin.tickets.notes.destroy');

        // Live Chat Management
        Route::get('live-chat', [AdminLiveChatController::class, 'index'])->name('live-chat.index');
        Route::get('live-chat/{ticketNumber}', [AdminLiveChatController::class, 'show'])->name('live-chat.show');
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
    });

    // Linked Accounts (Subscriptions sub-features)
    Route::middleware(['admin.permission:linked_accounts'])->group(function () {
        Route::middleware(['admin.subfeature:subscriptions,dashboard'])->group(function () {
            Route::get('linked-accounts', [LinkedAccountController::class, 'index'])->name('admin.linked-accounts.index');
        });
        Route::middleware(['admin.subfeature:subscriptions,starlinks'])->group(function () {
            Route::get('starlinks/import', [StarlinkController::class, 'importForm'])->name('admin.starlinks.import');
            Route::get('starlinks/import/template', [StarlinkController::class, 'importTemplate'])->name('admin.starlinks.import.template');
            Route::get('starlinks/export/csv', [StarlinkController::class, 'exportCsv'])->name('admin.starlinks.export.csv');
            Route::get('starlinks/export/pdf', [StarlinkController::class, 'exportPdf'])->name('admin.starlinks.export.pdf');
            Route::post('starlinks/import', [StarlinkController::class, 'processImport'])->name('admin.starlinks.import.process');
            Route::resource('starlinks', StarlinkController::class)->names('admin.starlinks');
        });
        Route::middleware(['admin.subfeature:subscriptions,omadas'])->group(function () {
            Route::get('omadas/import', [OmadaController::class, 'importForm'])->name('admin.omadas.import');
            Route::get('omadas/import/template', [OmadaController::class, 'importTemplate'])->name('admin.omadas.import.template');
            Route::post('omadas/import', [OmadaController::class, 'processImport'])->name('admin.omadas.import.process');
            Route::resource('omadas', OmadaController::class)->names('admin.omadas');
        });
        Route::middleware(['admin.subfeature:subscriptions,plan_types'])->group(function () {
            Route::resource('subscription-plan-types', SubscriptionPlanTypeController::class)->names('admin.subscription-plan-types');
        });
    });

    // System Management (landing page, stacks — parent system permission)
    Route::middleware(['admin.permission:system'])->group(function () {
        Route::middleware(['admin.subfeature:system,landing_page'])->group(function () {
            Route::get('/landing-page', [LandingPageController::class, 'index'])->name('admin.landing-page.index');
            Route::post('/landing-page', [LandingPageController::class, 'update'])->name('admin.landing-page.update');
        });

        Route::middleware(['admin.subfeature:system,stacks'])->group(function () {
            Route::resource('stacks', StackController::class)->names('admin.stacks');
        });

        // Status Management
        Route::get('status/online-users', [StatusController::class, 'getOnlineUsers'])->name('status.online-users');
        Route::get('status/away-users', [StatusController::class, 'getAwayUsers'])->name('status.away-users');
        Route::get('status/idle-users', [StatusController::class, 'getIdleUsers'])->name('status.idle-users');
        Route::get('status/all-users', [StatusController::class, 'getAllUserStatuses'])->name('status.all-users');
    });

    // Feedback Management – requires feedback permission (or Communication → Feedback via canAccessCommunicationFeature)
    Route::middleware(['admin.permission:feedback'])->group(function () {
        Route::resource('feedback', FeedbackController::class)->names('admin.feedback');
        Route::post('feedback/{feedback}/assign', [FeedbackController::class, 'assign'])->name('admin.feedback.assign');
        Route::get('feedback-stats', [FeedbackController::class, 'getStats'])->name('admin.feedback.stats');
        Route::get('feedback-admins', [FeedbackController::class, 'getAdmins'])->name('admin.feedback.admins');
    });
});

// User Routes
Route::middleware(['auth'])->group(function () {
    Route::post('/user/location', [UserGeoLocationController::class, 'store'])
        ->name('user.location.store');

    Route::get('/access', [AccountTerminatedController::class, 'show'])
        ->name('user.account-terminated');

    // Teachers only; kept on `auth` alone so access is not coupled to student termination checks.
    Route::get('/teacher/pending-applications', [UserDashboardController::class, 'teacherPendingApplications'])->name('user.teacher.pending-applications');
});

Route::middleware(['auth', 'student.not_terminated'])->group(function () {
    Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('user.dashboard');
    Route::post('/dashboard/rules-regulations/acknowledge', [UserDashboardController::class, 'acknowledgeRulesRegulations'])->name('user.rules-regulations.acknowledge');
    Route::post('/dashboard/system-announcement/acknowledge', [UserDashboardController::class, 'acknowledgeSystemAnnouncement'])->name('user.system-announcement.acknowledge');
    Route::get('/teacher/students', [UserDashboardController::class, 'teacherStudents'])->name('user.teacher.students');
    Route::get('/teacher/students/{user}/merits', [UserDashboardController::class, 'teacherStudentMeritDetails'])->name('user.teacher.students.merits');
    Route::get('/teacher/news', [UserDashboardController::class, 'teacherNews'])->name('user.teacher.news');
    Route::get('/teacher/excused-requests', [UserDashboardController::class, 'teacherExcusedRequests'])->name('user.teacher.excused-requests.index');
    Route::post('/teacher/excused-requests', [UserDashboardController::class, 'storeTeacherExcusedRequest'])->name('user.teacher.excused-requests.store');
    Route::get('/teacher/moa', [UserTeacherMoaController::class, 'index'])->name('user.teacher.moa.index');
    Route::post('/teacher/moa', [UserTeacherMoaController::class, 'store'])->name('user.teacher.moa.store');
    Route::get('/teacher/moa/preview', [UserTeacherMoaController::class, 'preview'])->name('user.teacher.moa.preview');
    Route::get('/technician/tickets', [TechnicianTicketController::class, 'index'])->name('user.technician-tickets.index');
    Route::patch('/technician/tickets/{ticket}', [TechnicianTicketController::class, 'update'])->name('user.technician-tickets.update');
    // TOR PDF for students
    Route::get('/tor', [UserDashboardController::class, 'tor'])->name('user.tor');

    // Student NDA
    Route::get('/nda', [App\Http\Controllers\User\StudentNdaController::class, 'index'])->name('user.nda.index');
    Route::post('/nda/generate-pdf', [App\Http\Controllers\User\StudentNdaController::class, 'generatePdf'])->name('user.nda.generate-pdf');
    Route::post('/nda', [App\Http\Controllers\User\StudentNdaController::class, 'store'])->name('user.nda.store');
    Route::get('/nda/preview', [App\Http\Controllers\User\StudentNdaController::class, 'preview'])->name('user.nda.preview');
    Route::get('/quizzes', [UserQuizController::class, 'index'])->name('user.quizzes.index');
    Route::get('/quizzes/enter-code', [UserQuizController::class, 'enterCode'])->name('user.quizzes.enter-code');
    Route::post('/quizzes/validate-code', [UserQuizController::class, 'validateCode'])->name('user.quizzes.validate-code');
    Route::get('/quizzes/{quiz}/take', [UserQuizController::class, 'take'])->name('user.quizzes.take');
    Route::get('/quizzes/{quiz}/questions', [UserQuizController::class, 'getQuestions'])->name('user.quizzes.questions');
    Route::post('/quizzes/{quiz}/start', [UserQuizController::class, 'start'])->name('user.quizzes.start');
    Route::post('/quizzes/{quiz}/save-progress', [UserQuizController::class, 'saveProgress'])->name('user.quizzes.save-progress');
    Route::post('/quizzes/{quiz}/submit', [UserQuizController::class, 'submit'])->name('user.quizzes.submit');
    Route::post('/quizzes/{quiz}/cancel', [UserQuizController::class, 'cancel'])->name('user.quizzes.cancel');
    Route::get('/quizzes/{quiz}/result', [UserQuizController::class, 'result'])->name('user.quizzes.result');
    Route::get('/quizzes/{quiz}/time-expired', [UserQuizController::class, 'timeExpired'])->name('user.quizzes.time-expired');

    // File Storage (User)
    Route::get('/files', [UserFileController::class, 'index'])->name('user.files.index');
    Route::post('/files/create-folder', [UserFileController::class, 'createFolder'])->name('user.files.create-folder');
    Route::post('/files', [UserFileController::class, 'store'])->name('user.files.store');
    Route::post('/files/presign', [UserFileController::class, 'presignUpload'])->name('user.files.presign');
    Route::post('/files/confirm', [UserFileController::class, 'confirmUpload'])->name('user.files.confirm');
    Route::post('/files/multipart/initiate', [UserFileController::class, 'initiateMultipartUpload'])->name('user.files.multipart.initiate');
    Route::post('/files/multipart/presign-chunk', [UserFileController::class, 'presignChunk'])->name('user.files.multipart.presign-chunk');
    Route::post('/files/multipart/upload-chunk', [UserFileController::class, 'uploadChunk'])->name('user.files.multipart.upload-chunk');
    Route::post('/files/multipart/complete', [UserFileController::class, 'completeMultipartUpload'])->name('user.files.multipart.complete');
    Route::post('/files/multipart/abort', [UserFileController::class, 'abortMultipartUpload'])->name('user.files.multipart.abort');
    Route::get('/files/{file}/download', [UserFileController::class, 'download'])->name('user.files.download');
    Route::get('/files/{file}/view', [UserFileController::class, 'view'])->name('user.files.view');
    Route::post('/files/{file}/share', [UserFileController::class, 'share'])->name('user.files.share');
    Route::post('/files/{file}/unshare', [UserFileController::class, 'unshare'])->name('user.files.unshare');
    Route::get('/files/{file}/shared-users', [UserFileController::class, 'getSharedUsers'])->name('user.files.shared-users');
    Route::delete('/files/{file}', [UserFileController::class, 'destroy'])->name('user.files.destroy');

    // Employee document requests (certificates, etc.)
    Route::get('/payslips', [App\Http\Controllers\User\PayslipController::class, 'index'])->name('user.payslips.index');
    Route::get('/payslips/{payslip}', [App\Http\Controllers\User\PayslipController::class, 'show'])->name('user.payslips.show');
    Route::post('/payslips/{payslip}/sign', [App\Http\Controllers\User\PayslipController::class, 'sign'])->name('user.payslips.sign');
    Route::get('/payslips/{payslip}/signed', [App\Http\Controllers\User\PayslipController::class, 'signedPdf'])->name('user.payslips.signed');

    Route::get('/document-requests', [EmployeeFileRequestController::class, 'index'])->name('user.employee-file-requests.index');
    Route::post('/document-requests', [EmployeeFileRequestController::class, 'store'])->name('user.employee-file-requests.store');
    Route::get('/document-requests/{employeeFileRequest}/view', [EmployeeFileRequestController::class, 'view'])->name('user.employee-file-requests.view');
    Route::get('/document-requests/{employeeFileRequest}/download', [EmployeeFileRequestController::class, 'download'])->name('user.employee-file-requests.download');

    Route::get('/documents', [App\Http\Controllers\User\EmployeeDocumentController::class, 'index'])->name('user.employee-documents.index');
    Route::get('/documents/{type}', [App\Http\Controllers\User\EmployeeDocumentController::class, 'show'])->name('user.employee-documents.show')->where('type', 'nda|contract|policy|handbook');
    Route::post('/documents/{type}/sign', [App\Http\Controllers\User\EmployeeDocumentController::class, 'sign'])->name('user.employee-documents.sign')->where('type', 'nda|contract|policy|handbook');
    Route::get('/documents/{type}/pdf', [App\Http\Controllers\User\EmployeeDocumentController::class, 'generatePdf'])->name('user.employee-documents.pdf')->where('type', 'nda|contract|policy|handbook');
    Route::get('/documents/{type}/preview', [App\Http\Controllers\User\EmployeeDocumentController::class, 'preview'])->name('user.employee-documents.preview')->where('type', 'nda|contract|policy|handbook');
    Route::get('/documents/handbook-material/{id}', [App\Http\Controllers\User\EmployeeDocumentController::class, 'handbookMaterial'])->name('user.employee-documents.handbook-material');
    Route::get('/documents/handbook-material/{id}/pdf', [App\Http\Controllers\User\EmployeeDocumentController::class, 'streamHandbookMaterial'])->name('user.employee-documents.handbook-material.pdf');
    Route::get('/documents/policy-material/{id}', [App\Http\Controllers\User\EmployeeDocumentController::class, 'policyMaterial'])->name('user.employee-documents.policy-material');
    Route::get('/documents/policy-material/{id}/pdf', [App\Http\Controllers\User\EmployeeDocumentController::class, 'streamPolicyMaterial'])->name('user.employee-documents.policy-material.pdf');

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
    // DTR (Employee Only)
    Route::get('/dtr', [App\Http\Controllers\User\DtrController::class, 'index'])->name('user.dtr.index');
    Route::get('/dtr/export-pdf', [App\Http\Controllers\User\DtrController::class, 'exportPdf'])->name('user.dtr.export-pdf');

    // Student Time Requests
    Route::post('/dtr-time-requests', [App\Http\Controllers\User\DtrTimeRequestController::class, 'store'])->name('user.dtr-time-requests.store');
    Route::delete('/dtr-time-requests/{dtrTimeRequest}', [App\Http\Controllers\User\DtrTimeRequestController::class, 'destroy'])->name('user.dtr-time-requests.destroy');

    Route::get('leave-requests/wfh-balance', [App\Http\Controllers\User\LeaveRequestController::class, 'wfhBalance'])
        ->name('user.leave-requests.wfh-balance');
    Route::get('leave-requests/{leaveRequest}/complete-attendance-overtime', [App\Http\Controllers\User\LeaveRequestController::class, 'completeAttendanceOvertime'])
        ->name('user.leave-requests.complete-attendance-overtime');
    Route::put('leave-requests/{leaveRequest}/complete-attendance-overtime', [App\Http\Controllers\User\LeaveRequestController::class, 'storeAttendanceOvertimeCompletion'])
        ->name('user.leave-requests.complete-attendance-overtime.update');
    Route::resource('leave-requests', App\Http\Controllers\User\LeaveRequestController::class)->names('user.leave-requests');

    // Hiring Application (Applicant Only)
    Route::get('/hiring-application', [App\Http\Controllers\User\HiringApplicationController::class, 'show'])->name('user.hiring-application.show');

    // Friendship Routes
    Route::get('/friends', [FriendshipController::class, 'index'])->name('friends.index');
    Route::get('/friends/users/{user}', [FriendshipController::class, 'show'])->name('friends.users.show');
    Route::get('/friends/search', [FriendshipController::class, 'search'])->name('friends.search');
    Route::post('/group-chats', [GroupChatController::class, 'store'])->name('group-chats.store');
    Route::post('/friends/send-request', [FriendshipController::class, 'sendRequest'])->name('friends.send-request');
    Route::post('/friends/{friendshipId}/accept', [FriendshipController::class, 'acceptRequest'])->name('friends.accept');
    Route::post('/friends/{friendshipId}/reject', [FriendshipController::class, 'rejectRequest'])->name('friends.reject');
    Route::post('/friends/{friendshipId}/cancel', [FriendshipController::class, 'cancelRequest'])->name('friends.cancel');
    Route::post('/friends/{friendId}/remove', [FriendshipController::class, 'removeFriend'])->name('friends.remove');
    Route::post('/friends/{userId}/block', [FriendshipController::class, 'blockUser'])->name('friends.block');

    // User Chat Routes
    Route::get('/user-chat', [UserChatController::class, 'index'])->name('user-chat.index');
    Route::middleware('throttle:chat-messages')->group(function () {
        Route::get('/user-chat/{friend}/messages', [UserChatController::class, 'getChat'])->name('user-chat.messages');
        Route::post('/user-chat/send', [UserChatController::class, 'sendMessage'])->name('user-chat.send');
        Route::get('/user-chat/unread-count', [UserChatController::class, 'getUnreadCount'])->name('user-chat.unread-count');
        Route::post('/user-chat/mark-read', [UserChatController::class, 'markAsRead'])->name('user-chat.mark-read');
        Route::get('/user-chat/recent', [UserChatController::class, 'getRecentChats'])->name('user-chat.recent');
        Route::get('/user-chat/sidebar-unread', [UserChatController::class, 'getSidebarUnread'])->name('user-chat.sidebar-unread');
        Route::get('/chat-media/{chatMessageMedia}', [ChatMediaController::class, 'show'])->name('chat-media.show');
        Route::post('/chat-media/{chatMessageMedia}/view', [ChatMediaController::class, 'markViewed'])->name('chat-media.view');
        Route::get('/stories/feed', [UserStoryController::class, 'feed'])->name('stories.feed');
        Route::post('/stories', [UserStoryController::class, 'store'])->name('stories.store');
        Route::get('/stories/user/{user}', [UserStoryController::class, 'userStories'])->name('stories.user');
        Route::get('/stories/{userStory}/media', [UserStoryController::class, 'media'])->name('stories.media');
        Route::post('/stories/{userStory}/view', [UserStoryController::class, 'markViewed'])->name('stories.view');
        Route::get('/group-chats/{groupChat}/messages', [GroupChatController::class, 'messages'])->name('group-chats.messages');
        Route::post('/group-chats/{groupChat}/messages', [GroupChatController::class, 'sendMessage'])->name('group-chats.send');
    });

    // Anonymous Chat Routes
    Route::get('/anonymous-chat', [AnonymousChatController::class, 'index'])->name('anonymous-chat.index');
    Route::get('/anonymous-chat/targets', [AnonymousChatController::class, 'targets'])
        ->middleware('throttle:anonymous-chat-targets')
        ->name('anonymous-chat.targets');
    Route::post('/anonymous-chat/start', [AnonymousChatController::class, 'store'])
        ->middleware('throttle:anonymous-chat-start')
        ->name('anonymous-chat.start');
    Route::middleware(['anonymous.chat.access', 'throttle:chat-messages'])->group(function () {
        Route::get('/anonymous-chat/{anonymousChatRoom}/messages', [AnonymousChatController::class, 'messages'])->name('anonymous-chat.messages');
        Route::post('/anonymous-chat/{anonymousChatRoom}/messages', [AnonymousChatController::class, 'sendMessage'])->name('anonymous-chat.send');
    });

    // Status Routes
    Route::post('/status/update', [StatusController::class, 'updateStatus'])->name('status.update');
    Route::get('/status', [StatusController::class, 'getStatus'])->name('status.get');

    // Profile Routes (Updated with new functionality)
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/password/change', [ProfileController::class, 'changePassword'])->name('profile.password.change');
    Route::delete('/profile/picture', [ProfileController::class, 'removeProfilePicture'])->name('profile.picture.remove');
    Route::delete('/profile/cover', [ProfileController::class, 'removeCoverPhoto'])->name('profile.cover.remove');
    Route::post('/profile/e-signature', [ProfileController::class, 'uploadESignature'])->name('profile.e-signature.upload');
    Route::delete('/profile/e-signature', [ProfileController::class, 'removeESignature'])->name('profile.e-signature.remove');
    Route::post('/profile/p12-certificate', [ProfileController::class, 'uploadP12Certificate'])->name('profile.p12-certificate.upload');
    Route::delete('/profile/p12-certificate', [ProfileController::class, 'removeP12Certificate'])->name('profile.p12-certificate.remove');

    // Feedback Routes
    Route::resource('feedback', App\Http\Controllers\User\FeedbackController::class)->names('user.feedback');
    Route::get('feedback-stats', [App\Http\Controllers\User\FeedbackController::class, 'getStats'])->name('user.feedback.stats');
    Route::get('/evaluation', [App\Http\Controllers\User\EvaluationController::class, 'create'])->name('user.evaluation.create');
    Route::post('/evaluation', [App\Http\Controllers\User\EvaluationController::class, 'store'])->name('user.evaluation.store');

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
