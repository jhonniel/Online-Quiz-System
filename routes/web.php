<?php

use App\Http\Controllers\Admin\AdminPermissionController;
use App\Http\Controllers\Admin\ContactMessageController as AdminContactMessageController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ErrorLogController;
use App\Http\Controllers\Admin\ImportController as AdminImportController;
use App\Http\Controllers\Admin\LiveChatController as AdminLiveChatController;
use App\Http\Controllers\Admin\QuizController as AdminQuizController;
use App\Http\Controllers\Admin\StudentDashboardController;
use App\Http\Controllers\Admin\TeacherExcusedRequestController as AdminTeacherExcusedRequestController;
use App\Http\Controllers\Admin\TeacherMoaController as AdminTeacherMoaController;
use App\Http\Controllers\Admin\UniversityController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\RoleLoginController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\RedirectController;
use App\Http\Controllers\SayItComfyUiProxyController;
use App\Http\Controllers\SayItSdWebUiProxyController;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\TeacherInviteController;
use App\Http\Controllers\User\DashboardController as UserDashboardController;
use App\Http\Controllers\User\FileController as UserFileController;
use App\Http\Controllers\User\QuizController as UserQuizController;
use App\Http\Controllers\User\TeacherMoaController as UserTeacherMoaController;
use App\Http\Middleware\VerifySayItComfyUiProxySecret;
use App\Http\Middleware\VerifySayItSdWebUiProxySecret;
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
Route::get('/report-problem', [App\Http\Controllers\ReportProblemController::class, 'show'])->name('report-problem');
Route::post('/report-problem', [App\Http\Controllers\ReportProblemController::class, 'store'])->name('report-problem.store');
Route::get('/image-proxy/{path}', [LandingController::class, 'imageProxy'])->where('path', '.*')->name('landing.image-proxy');
Route::get('/privacy-policy', [LandingController::class, 'privacyPolicy'])->name('landing.privacy-policy');
Route::get('/tor-pdf', [LandingController::class, 'torPdf'])->name('landing.tor-pdf');

// Say-it: Anonymous confession board (no login, /Say-it only)
Route::get('/Say-it', [App\Http\Controllers\SayItController::class, 'index']);
Route::post('/Say-it', [App\Http\Controllers\SayItController::class, 'storePost']);
Route::post('/Say-it/generate-image', [App\Http\Controllers\SayItController::class, 'generateImage'])
    ->middleware('throttle:say-it-ai-image');
Route::get('/Say-it/composer-mesh-preview', [App\Http\Controllers\SayItController::class, 'composerMeshPreview'])
    ->middleware('throttle:60,1');
Route::get('/Say-it/{post}', [App\Http\Controllers\SayItController::class, 'show'])->where('post', '[0-9]+');
Route::post('/Say-it/comment', [App\Http\Controllers\SayItController::class, 'storeComment']);
Route::post('/Say-it/vote', [App\Http\Controllers\SayItController::class, 'vote']);
Route::delete('/Say-it/post/{post}', [App\Http\Controllers\SayItController::class, 'destroyPost'])->where('post', '[0-9]+')->name('say-it.post.delete');

// QR Code Scanning Route (Public) - Uses hashed token for one-time access
Route::get('/qr/{token}', [App\Http\Controllers\QrCodeController::class, 'scan'])->name('qr.scan');

// Public Hiring Application Routes (dynamic URL based on admin settings)
// The route will be registered dynamically in the controller based on settings
Route::get('/hiring/accept/{token}', [App\Http\Controllers\HiringApplicationController::class, 'acceptWithToken'])->name('hiring.accept');
Route::get('/hiring/application/success', [App\Http\Controllers\HiringApplicationController::class, 'success'])->name('hiring.application.success');

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
            Route::get('/settings', [\App\Http\Controllers\Admin\SettingsController::class, 'index'])->name('admin.settings.index');
            Route::post('/settings', [\App\Http\Controllers\Admin\SettingsController::class, 'update'])->name('admin.settings.update');
            Route::get('/settings/health', [\App\Http\Controllers\Admin\SettingsController::class, 'getHealth'])->name('admin.settings.health');
            Route::get('/settings/health-metrics', [\App\Http\Controllers\Admin\SettingsController::class, 'getHealthMetrics'])->name('admin.settings.health-metrics');
            Route::post('/settings/test-email', [\App\Http\Controllers\Admin\SettingsController::class, 'testEmail'])->name('admin.settings.test-email');
        });
        Route::middleware(['admin.subfeature:system,rules'])->group(function () {
            Route::get('/system/rules', [\App\Http\Controllers\Admin\SettingsController::class, 'rulesRegulations'])->name('admin.system.rules');
            Route::post('/system/rules', [\App\Http\Controllers\Admin\SettingsController::class, 'updateRulesRegulations'])->name('admin.system.rules.update');
            Route::post('/system/rules/merit-notices', [\App\Http\Controllers\Admin\SettingsController::class, 'updateMeritNoticeSettings'])->name('admin.system.rules.merit-notices.update');
        });
        Route::middleware(['admin.subfeature:system,api_monitoring'])->group(function () {
            Route::get('/system/api-monitoring', [\App\Http\Controllers\Admin\ApiMonitoringController::class, 'index'])->name('admin.system.api-monitoring.index');
            Route::get('/system/api-monitoring/metrics', [\App\Http\Controllers\Admin\ApiMonitoringController::class, 'metrics'])->name('admin.system.api-monitoring.metrics');
            Route::post('/system/api-monitoring/external-access', [\App\Http\Controllers\Admin\ApiMonitoringController::class, 'updateExternalAccess'])->name('admin.system.api-monitoring.external-access');
            Route::post('/system/api-monitoring/external-allowed-apis', [\App\Http\Controllers\Admin\ApiMonitoringController::class, 'updateExternalAllowedApis'])->name('admin.system.api-monitoring.external-allowed-apis');
            Route::post('/system/api-monitoring/keys', [\App\Http\Controllers\Admin\ApiMonitoringController::class, 'createApiKey'])->name('admin.system.api-monitoring.keys.store');
            Route::post('/system/api-monitoring/keys/{key}/revoke', [\App\Http\Controllers\Admin\ApiMonitoringController::class, 'revokeApiKey'])->name('admin.system.api-monitoring.keys.revoke');
        });
        Route::middleware(['admin.subfeature:system,network_graph'])->group(function () {
            Route::get('/system/network-graph', [\App\Http\Controllers\Admin\NetworkGraphController::class, 'index'])->name('admin.system.network-graph.index');
            Route::get('/system/network-graph/data', [\App\Http\Controllers\Admin\NetworkGraphController::class, 'graphData'])->name('admin.system.network-graph.data');
            Route::post('/system/network-graph/sync', [\App\Http\Controllers\Admin\NetworkGraphController::class, 'sync'])->name('admin.system.network-graph.sync');
            Route::post('/system/network-graph/clear', [\App\Http\Controllers\Admin\NetworkGraphController::class, 'clear'])->name('admin.system.network-graph.clear');
        });
    });

    // Billing (Subscriptions → Billing sub-feature)
    Route::middleware(['admin.permission:billing', 'admin.subfeature:subscriptions,billing'])->group(function () {
        Route::get('billing', [App\Http\Controllers\Admin\BillingController::class, 'index'])->name('admin.billing.index');
        Route::post('billing/mark-paid', [App\Http\Controllers\Admin\BillingController::class, 'markAsPaid'])->name('admin.billing.mark-paid');
        Route::post('billing/advance-payment', [App\Http\Controllers\Admin\BillingController::class, 'markAdvancePayment'])->name('admin.billing.advance-payment');
        Route::get('billing/statement/{billingStatement}', [App\Http\Controllers\Admin\BillingController::class, 'showStatement'])->name('admin.billing.statement');
        Route::get('billing/statement/{billingStatement}/pdf', [App\Http\Controllers\Admin\BillingController::class, 'downloadPdf'])->name('admin.billing.statement.pdf');
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
            Route::resource('departments', App\Http\Controllers\Admin\DepartmentController::class)->names('admin.departments');
            Route::patch('departments/{department}/toggle-status', [App\Http\Controllers\Admin\DepartmentController::class, 'toggleStatus'])->name('admin.departments.toggle-status');
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

        // Task Management - Dashboard and Analytics (Super Admin Only)
        Route::get('tasks/dashboard', [App\Http\Controllers\Admin\TaskController::class, 'dashboard'])->name('admin.tasks.dashboard');
        Route::get('tasks/dashboard/chart-data', [App\Http\Controllers\Admin\TaskController::class, 'getChartData'])->name('admin.tasks.dashboard.chart-data');
        // Task Management - My Tasks and Group Tasks (Available to Students and Employees)
        Route::get('tasks', [App\Http\Controllers\Admin\TaskController::class, 'index'])->name('admin.tasks.index');
        Route::post('tasks', [App\Http\Controllers\Admin\TaskController::class, 'store'])->name('admin.tasks.store');
        Route::put('tasks/{task}', [App\Http\Controllers\Admin\TaskController::class, 'update'])->name('admin.tasks.update');
        Route::post('tasks/{task}/reorder', [App\Http\Controllers\Admin\TaskController::class, 'reorder'])->name('admin.tasks.reorder');
        Route::delete('tasks/{task}', [App\Http\Controllers\Admin\TaskController::class, 'destroy'])->name('admin.tasks.destroy');
        Route::post('tasks/update-order', [App\Http\Controllers\Admin\TaskController::class, 'updateOrder'])->name('admin.tasks.update-order');
        Route::post('tasks/{task}/comments', [App\Http\Controllers\Admin\TaskController::class, 'addComment'])->name('admin.tasks.add-comment');
        Route::post('tasks/{task}/attachments', [App\Http\Controllers\Admin\TaskController::class, 'uploadAttachment'])->name('admin.tasks.upload-attachment');
        Route::delete('tasks/attachments/{attachment}', [App\Http\Controllers\Admin\TaskController::class, 'deleteAttachment'])->name('admin.tasks.delete-attachment');

        Route::middleware(['admin.subfeature:content_management,news'])->group(function () {
        // News Management
        Route::resource('news', App\Http\Controllers\Admin\NewsController::class)->names([
            'index' => 'admin.news.index',
            'create' => 'admin.news.create',
            'store' => 'admin.news.store',
            'show' => 'admin.news.show',
            'edit' => 'admin.news.edit',
            'update' => 'admin.news.update',
            'destroy' => 'admin.news.destroy',
        ]);
        Route::post('news/{news}/toggle-publish', [App\Http\Controllers\Admin\NewsController::class, 'togglePublish'])->name('admin.news.toggle-publish');
        Route::post('news/toggle-section', [App\Http\Controllers\Admin\NewsController::class, 'toggleNewsSection'])->name('admin.news.toggle-section');
        });

        Route::post('tasks/{task}/assign-users', [App\Http\Controllers\Admin\TaskController::class, 'assignUsers'])->name('admin.tasks.assign-users');

        // Task Invitation Routes
        Route::post('tasks/{task}/generate-invite-code', [App\Http\Controllers\Admin\TaskController::class, 'generateInviteCode'])->name('admin.tasks.generate-invite-code');
        Route::post('tasks/{task}/get-invite-link', [App\Http\Controllers\Admin\TaskController::class, 'getInviteLink'])->name('admin.tasks.get-invite-link');
        Route::post('tasks/{task}/generate-share-link', [App\Http\Controllers\Admin\TaskController::class, 'generateShareLink'])->name('admin.tasks.generate-share-link');
        Route::post('tasks/{task}/invite-users', [App\Http\Controllers\Admin\TaskController::class, 'inviteUsers'])->name('admin.tasks.invite-users');
        Route::post('tasks/join-by-code', [App\Http\Controllers\Admin\TaskController::class, 'joinByCode'])->name('admin.tasks.join-by-code')->middleware('auth');
        Route::get('tasks/join-by-link/{token}', [App\Http\Controllers\Admin\TaskController::class, 'joinByLink'])->name('admin.tasks.join-by-link')->middleware('auth');
        Route::post('tasks/invitations/{invitation}/accept', [App\Http\Controllers\Admin\TaskController::class, 'acceptInvitation'])->name('admin.tasks.invitations.accept');
        Route::post('tasks/invitations/{invitation}/reject', [App\Http\Controllers\Admin\TaskController::class, 'rejectInvitation'])->name('admin.tasks.invitations.reject');
        Route::get('tasks/pending-invitations', [App\Http\Controllers\Admin\TaskController::class, 'getPendingInvitations'])->name('admin.tasks.pending-invitations');
        Route::post('tasks/{task}/convert-to-group', [App\Http\Controllers\Admin\TaskController::class, 'convertToGroup'])->name('admin.tasks.convert-to-group');

        // Custom Boards Management
        Route::post('tasks/custom-boards', [App\Http\Controllers\Admin\TaskController::class, 'storeCustomBoard'])->name('admin.tasks.custom-boards.store');
        Route::put('tasks/custom-boards/{customBoard}', [App\Http\Controllers\Admin\TaskController::class, 'updateCustomBoard'])->name('admin.tasks.custom-boards.update');
        Route::delete('tasks/custom-boards/{customBoard}', [App\Http\Controllers\Admin\TaskController::class, 'destroyCustomBoard'])->name('admin.tasks.custom-boards.destroy');
        Route::post('tasks/custom-boards/update-order', [App\Http\Controllers\Admin\TaskController::class, 'updateCustomBoardOrder'])->name('admin.tasks.custom-boards.update-order');
        Route::post('tasks/custom-boards/{customBoard}/toggle-lock', [App\Http\Controllers\Admin\TaskController::class, 'toggleCustomBoardLock'])->name('admin.tasks.custom-boards.toggle-lock');

        // Task List Routes (for personal tasks)
        Route::post('tasks/task-lists', [App\Http\Controllers\Admin\TaskController::class, 'storeTaskList'])->name('admin.tasks.task-lists.store');
        Route::put('tasks/task-lists/{taskList}', [App\Http\Controllers\Admin\TaskController::class, 'updateTaskList'])->name('admin.tasks.task-lists.update');
        Route::delete('tasks/task-lists/{taskList}', [App\Http\Controllers\Admin\TaskController::class, 'destroyTaskList'])->name('admin.tasks.task-lists.destroy');

        // Task List Sharing Routes
        Route::post('tasks/task-lists/{taskList}/generate-invite-code', [App\Http\Controllers\Admin\TaskController::class, 'generateTaskListInviteCode'])->name('admin.tasks.task-lists.generate-invite-code');
        Route::post('tasks/task-lists/{taskList}/generate-share-link', [App\Http\Controllers\Admin\TaskController::class, 'generateTaskListShareLink'])->name('admin.tasks.task-lists.generate-share-link');
        Route::post('tasks/task-lists/{taskList}/send-invitation-email', [App\Http\Controllers\Admin\TaskController::class, 'sendTaskListInvitationEmail'])->name('admin.tasks.task-lists.send-invitation-email');
        Route::post('tasks/task-lists/join-by-code', [App\Http\Controllers\Admin\TaskController::class, 'joinTaskListByCode'])->name('admin.tasks.task-lists.join-by-code')->middleware('auth');
        Route::get('tasks/task-lists/join-by-link/{token}', [App\Http\Controllers\Admin\TaskController::class, 'joinTaskListByLink'])->name('admin.tasks.join-task-list-by-link')->middleware('auth');

        // Custom Priority Routes
        Route::post('tasks/custom-priorities', [App\Http\Controllers\Admin\TaskController::class, 'storeCustomPriority'])->name('admin.tasks.custom-priorities.store');
        Route::put('tasks/custom-priorities/{customPriority}', [App\Http\Controllers\Admin\TaskController::class, 'updateCustomPriority'])->name('admin.tasks.custom-priorities.update');
        Route::delete('tasks/custom-priorities/{customPriority}', [App\Http\Controllers\Admin\TaskController::class, 'destroyCustomPriority'])->name('admin.tasks.custom-priorities.destroy');

        // Import Management (Standalone Import Page)
        Route::get('import', [AdminImportController::class, 'index'])->name('admin.import');
        Route::post('import', [AdminImportController::class, 'import'])->name('admin.import.process');
        Route::get('import/template', [AdminImportController::class, 'downloadTemplate'])->name('admin.import.template');

        Route::middleware(['admin.subfeature:content_management,forum'])->group(function () {
            Route::resource('forum', App\Http\Controllers\Admin\ForumController::class)->names('admin.forum');
            Route::patch('forum/{forum}/toggle-publish', [App\Http\Controllers\Admin\ForumController::class, 'togglePublish'])->name('admin.forum.toggle-publish');
            Route::patch('forum/{forum}/toggle-pin', [App\Http\Controllers\Admin\ForumController::class, 'togglePin'])->name('admin.forum.toggle-pin');
            Route::post('forum/comment', [App\Http\Controllers\Admin\ForumController::class, 'comment'])->name('admin.forum.comment');
            Route::post('forum/comment/like', [App\Http\Controllers\Admin\ForumController::class, 'likeComment'])->name('admin.forum.comment.like');
        });

        Route::middleware(['admin.subfeature:content_management,evaluations'])->group(function () {
            Route::resource('evaluations', App\Http\Controllers\Admin\EvaluationController::class)->except(['show'])->names('admin.evaluations');
            Route::post('evaluations/{evaluation}/activate', [App\Http\Controllers\Admin\EvaluationController::class, 'activate'])->name('admin.evaluations.activate');
            Route::get('evaluations/{evaluation}/submissions', [App\Http\Controllers\Admin\EvaluationController::class, 'submissions'])->name('admin.evaluations.submissions');
            Route::post('evaluations/force-send', [App\Http\Controllers\Admin\EvaluationController::class, 'forceSend'])->name('admin.evaluations.force-send');
        });
    });

    // Analytics & Reports (parent: analytics_reports; sub-areas: admin.analytics:{feature})
    Route::middleware(['admin.permission:analytics_reports'])->group(function () {
        Route::middleware(['admin.analytics:analytics'])->group(function () {
            Route::get('analytics', [App\Http\Controllers\Admin\AnalyticsController::class, 'index'])->name('admin.analytics.index');
            Route::get('analytics/quiz/{quizId}', [App\Http\Controllers\Admin\AnalyticsController::class, 'getQuizDetails'])->name('admin.analytics.quiz-details');
            Route::get('analytics/student/{userId}', [App\Http\Controllers\Admin\AnalyticsController::class, 'getStudentDetails'])->name('admin.analytics.student-details');
            Route::get('analytics/topic/{topic}', [App\Http\Controllers\Admin\AnalyticsController::class, 'getTopicDetails'])->name('admin.analytics.topic-details')->where('topic', '.*');
        });
        Route::middleware(['admin.analytics:error_logs'])->group(function () {
            Route::get('analytics/error-logs', [ErrorLogController::class, 'index'])->name('admin.analytics.error-logs');
        });
        Route::middleware(['admin.analytics:students_review'])->group(function () {
            Route::get('analytics/students-review', [App\Http\Controllers\Admin\EvaluationController::class, 'reviews'])->name('admin.evaluations.reviews');
        });
    });

    // User Activity: analytics sub-permission or legacy System permission
    Route::middleware(['admin.analytics:user_activity'])->group(function () {
        Route::get('user-activity', [App\Http\Controllers\Admin\UserActivityController::class, 'index'])->name('admin.user-activity.index');
        Route::get('user-activity/sessions', [App\Http\Controllers\Admin\UserActivityController::class, 'sessions'])->name('admin.user-activity.sessions');
        Route::get('user-activity/statistics', [App\Http\Controllers\Admin\UserActivityController::class, 'statistics'])->name('admin.user-activity.statistics');
        Route::post('user-activity/cleanup', [App\Http\Controllers\Admin\UserActivityController::class, 'cleanup'])->name('admin.user-activity.cleanup');
    });

    // Key Performance Indicator (KPI) - Only for super admins
    Route::middleware(['auth'])->group(function () {
        Route::get('kpi/dashboard', [App\Http\Controllers\Admin\KpiController::class, 'dashboard'])->name('admin.kpi.dashboard');
    });

    // Employee Management
    Route::middleware(['admin.permission:employee_management'])->group(function () {
        Route::middleware(['admin.subfeature:employee_management,employee_dashboard'])->group(function () {
            Route::get('/employee-dashboard', [App\Http\Controllers\Admin\EmployeeDashboardController::class, 'index'])->name('admin.employee-dashboard.index');
        });

        Route::middleware(['admin.subfeature:employee_management,file_request'])->group(function () {
            Route::get('/file-request', [App\Http\Controllers\Admin\FileRequestController::class, 'index'])->name('admin.file-request.index');
            Route::post('/file-request/preview', [App\Http\Controllers\Admin\FileRequestController::class, 'preview'])->name('admin.file-request.preview');
            Route::post('/file-request/generate', [App\Http\Controllers\Admin\FileRequestController::class, 'generate'])->name('admin.file-request.generate');
            Route::get('/file-request/history/{fileRequest}/view', [App\Http\Controllers\Admin\FileRequestController::class, 'view'])->name('admin.file-request.view');
            Route::get('/file-request/history/{fileRequest}/download', [App\Http\Controllers\Admin\FileRequestController::class, 'download'])->name('admin.file-request.download');
            Route::delete('/file-request/history/{fileRequest}', [App\Http\Controllers\Admin\FileRequestController::class, 'destroy'])->name('admin.file-request.destroy');
            Route::get('/file-request/templates', [App\Http\Controllers\Admin\FileRequestController::class, 'templatesIndex'])->name('admin.file-request.templates.index');
            Route::get('/file-request/templates/create', [App\Http\Controllers\Admin\FileRequestController::class, 'templatesCreate'])->name('admin.file-request.templates.create');
            Route::post('/file-request/templates', [App\Http\Controllers\Admin\FileRequestController::class, 'templatesStore'])->name('admin.file-request.templates.store');
            Route::get('/file-request/templates/{template}/edit', [App\Http\Controllers\Admin\FileRequestController::class, 'templatesEdit'])->name('admin.file-request.templates.edit');
            Route::put('/file-request/templates/{template}', [App\Http\Controllers\Admin\FileRequestController::class, 'templatesUpdate'])->name('admin.file-request.templates.update');
            Route::delete('/file-request/templates/{template}', [App\Http\Controllers\Admin\FileRequestController::class, 'templatesDestroy'])->name('admin.file-request.templates.destroy');
        });

        Route::middleware(['admin.subfeature:employee_management,dtr'])->group(function () {
        // DTR Management (Employees)
        Route::get('/dtr', [App\Http\Controllers\Admin\DtrController::class, 'index'])->name('admin.dtr.index');
        Route::get('/dtr/create', [App\Http\Controllers\Admin\DtrController::class, 'create'])->name('admin.dtr.create');
        Route::post('/dtr', [App\Http\Controllers\Admin\DtrController::class, 'store'])->name('admin.dtr.store');
        Route::get('/dtr/{dtr}/edit', [App\Http\Controllers\Admin\DtrController::class, 'edit'])->name('admin.dtr.edit');
        Route::put('/dtr/{dtr}', [App\Http\Controllers\Admin\DtrController::class, 'update'])->name('admin.dtr.update');
        Route::delete('/dtr/{dtr}', [App\Http\Controllers\Admin\DtrController::class, 'destroy'])->name('admin.dtr.destroy');
        Route::post('/dtr/recalculate-deficits', [App\Http\Controllers\Admin\DtrController::class, 'recalculateDeficits'])->name('admin.dtr.recalculate-deficits');
        Route::post('/dtr/import', [App\Http\Controllers\Admin\DtrController::class, 'import'])->name('admin.dtr.import');
        Route::get('/dtr/template', [App\Http\Controllers\Admin\DtrController::class, 'downloadTemplate'])->name('admin.dtr.template');
        Route::get('/dtr/export-pdf', [App\Http\Controllers\Admin\DtrController::class, 'exportPdf'])->name('admin.dtr.export-pdf');
        });

        Route::middleware(['admin.subfeature:employee_management,time_report'])->group(function () {
            Route::get('/time-report', [App\Http\Controllers\Admin\TimeReportController::class, 'index'])->name('admin.time-report.index');
        });

        Route::middleware(['admin.subfeature:employee_management,leave_requests'])->group(function () {
        // Leave Requests Management (Employees)
        Route::get('/leave-requests', [App\Http\Controllers\Admin\LeaveRequestController::class, 'index'])->name('admin.leave-requests.index');
        Route::post('/leave-requests/create-for-employee', [App\Http\Controllers\Admin\LeaveRequestController::class, 'storeForEmployee'])->name('admin.leave-requests.store-for-employee');
        Route::get('/leave-requests/{leaveRequest}', [App\Http\Controllers\Admin\LeaveRequestController::class, 'show'])->name('admin.leave-requests.show');
        Route::patch('/leave-requests/{leaveRequest}/type', [App\Http\Controllers\Admin\LeaveRequestController::class, 'updateType'])->name('admin.leave-requests.update-type');
        Route::patch('/leave-requests/{leaveRequest}/dates', [App\Http\Controllers\Admin\LeaveRequestController::class, 'updateDates'])->name('admin.leave-requests.update-dates');
        Route::post('/leave-requests/{leaveRequest}/approve', [App\Http\Controllers\Admin\LeaveRequestController::class, 'approve'])->name('admin.leave-requests.approve');
        Route::post('/leave-requests/{leaveRequest}/verify', [App\Http\Controllers\Admin\LeaveRequestController::class, 'verify'])->name('admin.leave-requests.verify');
        Route::post('/leave-requests/{leaveRequest}/force-accept', [App\Http\Controllers\Admin\LeaveRequestController::class, 'forceAccept'])->name('admin.leave-requests.force-accept');
        Route::post('/leave-requests/{leaveRequest}/reject', [App\Http\Controllers\Admin\LeaveRequestController::class, 'reject'])->name('admin.leave-requests.reject');
        Route::post('/leave-requests/{leaveRequest}/resubmit', [App\Http\Controllers\Admin\LeaveRequestController::class, 'resubmit'])->name('admin.leave-requests.resubmit');
        Route::delete('/leave-requests/{leaveRequest}', [App\Http\Controllers\Admin\LeaveRequestController::class, 'destroy'])->name('admin.leave-requests.destroy');
        });

        Route::middleware(['admin.subfeature:employee_management,leave_calendar'])->group(function () {
            Route::get('/leave-calendar', [App\Http\Controllers\Admin\LeaveRequestController::class, 'calendar'])->name('admin.leave-requests.calendar');
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
        Route::get('/student-dtr', [App\Http\Controllers\Admin\DtrController::class, 'studentIndex'])->name('admin.student-dtr.index');
        Route::get('/student-dtr/create', [App\Http\Controllers\Admin\DtrController::class, 'studentCreate'])->name('admin.student-dtr.create');
        Route::post('/student-dtr', [App\Http\Controllers\Admin\DtrController::class, 'studentStore'])->name('admin.student-dtr.store');
        Route::get('/student-dtr/{dtr}/edit', [App\Http\Controllers\Admin\DtrController::class, 'studentEdit'])->name('admin.student-dtr.edit');
        Route::put('/student-dtr/{dtr}', [App\Http\Controllers\Admin\DtrController::class, 'studentUpdate'])->name('admin.student-dtr.update');
        Route::delete('/student-dtr/{dtr}', [App\Http\Controllers\Admin\DtrController::class, 'studentDestroy'])->name('admin.student-dtr.destroy');
        Route::post('/student-dtr/bulk-update', [App\Http\Controllers\Admin\DtrController::class, 'studentBulkUpdate'])->name('admin.student-dtr.bulk-update');
        Route::post('/student-dtr/bulk-delete', [App\Http\Controllers\Admin\DtrController::class, 'studentBulkDelete'])->name('admin.student-dtr.bulk-delete');
        Route::get('/student-dtr/export/pdf', [App\Http\Controllers\Admin\DtrController::class, 'studentExportPdf'])->name('admin.student-dtr.export-pdf');

        // Student Leave Requests Management
        Route::get('/student-leave-requests', [App\Http\Controllers\Admin\LeaveRequestController::class, 'studentIndex'])->name('admin.student-leave-requests.index');
        Route::get('/student-leave-calendar', [App\Http\Controllers\Admin\LeaveRequestController::class, 'studentCalendar'])->name('admin.student-leave-requests.calendar');
        Route::post('/student-leave-requests/create-for-student', [App\Http\Controllers\Admin\LeaveRequestController::class, 'storeForStudent'])->name('admin.student-leave-requests.store-for-student');

        // Student Time Requests Management
        Route::get('/time-requests', [App\Http\Controllers\Admin\DtrTimeRequestController::class, 'index'])->name('admin.time-requests.index');
        Route::put('/time-requests/{dtrTimeRequest}', [App\Http\Controllers\Admin\DtrTimeRequestController::class, 'update'])->name('admin.time-requests.update');
        Route::post('/time-requests/{dtrTimeRequest}/approve', [App\Http\Controllers\Admin\DtrTimeRequestController::class, 'approve'])->name('admin.time-requests.approve');
        Route::post('/time-requests/{dtrTimeRequest}/reject', [App\Http\Controllers\Admin\DtrTimeRequestController::class, 'reject'])->name('admin.time-requests.reject');
        Route::delete('/time-requests/{dtrTimeRequest}', [App\Http\Controllers\Admin\DtrTimeRequestController::class, 'destroy'])->name('admin.time-requests.destroy');
    });

    // Hiring Process Management
    Route::middleware(['admin.permission:hiring_process'])->group(function () {
        Route::get('/hiring-process', [App\Http\Controllers\Admin\HiringProcessController::class, 'index'])->name('admin.hiring-process.index');
        Route::get('/hiring-process/applicants', [App\Http\Controllers\Admin\HiringProcessController::class, 'applicants'])->name('admin.hiring-process.applicants');

        // Hiring Positions Management
        Route::resource('hiring-positions', App\Http\Controllers\Admin\HiringPositionController::class)->names('admin.hiring-positions');
        Route::patch('hiring-positions/{hiringPosition}/toggle-status', [App\Http\Controllers\Admin\HiringPositionController::class, 'toggleStatus'])->name('admin.hiring-positions.toggle-status');

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
        Route::get('files', [App\Http\Controllers\Admin\FileController::class, 'index'])->name('admin.files.index');
        Route::post('files', [App\Http\Controllers\Admin\FileController::class, 'store'])->name('admin.files.store');
        Route::post('files/presign', [App\Http\Controllers\Admin\FileController::class, 'presignUpload'])->name('admin.files.presign');
        Route::post('files/confirm', [App\Http\Controllers\Admin\FileController::class, 'confirmUpload'])->name('admin.files.confirm');
        Route::post('files/multipart/initiate', [App\Http\Controllers\Admin\FileController::class, 'initiateMultipartUpload'])->name('admin.files.multipart.initiate');
        Route::post('files/multipart/presign-chunk', [App\Http\Controllers\Admin\FileController::class, 'presignChunk'])->name('admin.files.multipart.presign-chunk');
        Route::post('files/multipart/upload-chunk', [App\Http\Controllers\Admin\FileController::class, 'uploadChunk'])->name('admin.files.multipart.upload-chunk');
        Route::post('files/multipart/complete', [App\Http\Controllers\Admin\FileController::class, 'completeMultipartUpload'])->name('admin.files.multipart.complete');
        Route::post('files/multipart/abort', [App\Http\Controllers\Admin\FileController::class, 'abortMultipartUpload'])->name('admin.files.multipart.abort');
        Route::post('files/create-folder', [App\Http\Controllers\Admin\FileController::class, 'createFolder'])->name('admin.files.create-folder');
        Route::put('files/{file}', [App\Http\Controllers\Admin\FileController::class, 'update'])->name('admin.files.update');
        Route::delete('files/{file}', [App\Http\Controllers\Admin\FileController::class, 'destroy'])->name('admin.files.destroy');
        Route::get('files/{file}/download', [App\Http\Controllers\Admin\FileController::class, 'download'])->name('admin.files.download');
        Route::get('files/{file}/view', [App\Http\Controllers\Admin\FileController::class, 'view'])->name('admin.files.view');
        Route::post('files/{file}/share', [App\Http\Controllers\Admin\FileController::class, 'share'])->name('admin.files.share');
        Route::post('files/{file}/unshare', [App\Http\Controllers\Admin\FileController::class, 'unshare'])->name('admin.files.unshare');
        Route::get('files/{file}/shared-users', [App\Http\Controllers\Admin\FileController::class, 'getSharedUsers'])->name('admin.files.shared-users');
    });

    // Confession (Say-it) – requires confession permission
    Route::middleware(['admin.permission:confession'])->group(function () {
        Route::get('confession', [App\Http\Controllers\Admin\ConfessionController::class, 'index']);
        Route::get('confession/dashboard', [App\Http\Controllers\Admin\ConfessionController::class, 'dashboard']);
        Route::get('confession/topics', [App\Http\Controllers\Admin\ConfessionController::class, 'topics'])->name('admin.confession.topics');
        Route::get('confession/topics/{confession_topic}/edit', [App\Http\Controllers\Admin\ConfessionController::class, 'editTopic'])->name('admin.confession.topics.edit');
        Route::put('confession/topics/{confession_topic}', [App\Http\Controllers\Admin\ConfessionController::class, 'updateTopic'])->name('admin.confession.topics.update');
        Route::delete('confession/topics/{confession_topic}', [App\Http\Controllers\Admin\ConfessionController::class, 'destroyTopic'])->name('admin.confession.topics.destroy');
        Route::delete('confession/posts/{confession_post}', [App\Http\Controllers\Admin\ConfessionController::class, 'destroy']);
        Route::post('confession/anon-name-settings', [App\Http\Controllers\Admin\ConfessionController::class, 'updateAnonNameSettings'])->name('admin.confession.anon-name-settings');
        Route::get('confession/banned-words', [App\Http\Controllers\Admin\ConfessionBannedWordController::class, 'index'])->name('admin.confession.banned-words');
        Route::post('confession/banned-words', [App\Http\Controllers\Admin\ConfessionBannedWordController::class, 'store']);
        Route::delete('confession/banned-words/{banned_word}', [App\Http\Controllers\Admin\ConfessionBannedWordController::class, 'destroy'])->name('admin.confession.banned-words.destroy');
    });

    // Communication
    Route::middleware(['admin.permission:communication'])->group(function () {

        // Notification Management
        Route::get('notifications/unread', [App\Http\Controllers\Admin\NotificationController::class, 'getUnread'])->name('admin.notifications.unread');
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

        // Ticket Reports (problem reports from /report-problem)
        Route::get('tickets', [App\Http\Controllers\Admin\TicketReportController::class, 'dashboard'])->name('admin.tickets.dashboard');
        Route::get('tickets/open', [App\Http\Controllers\Admin\TicketReportController::class, 'open'])->name('admin.tickets.open');
        Route::get('tickets/closed', [App\Http\Controllers\Admin\TicketReportController::class, 'closed'])->name('admin.tickets.closed');
        Route::get('tickets/problem-types', [App\Http\Controllers\Admin\TicketProblemTypeController::class, 'index'])->name('admin.tickets.problem-types.index');
        Route::post('tickets/problem-types', [App\Http\Controllers\Admin\TicketProblemTypeController::class, 'store'])->name('admin.tickets.problem-types.store');
        Route::get('tickets/problem-types/{ticket_problem_type}/edit', [App\Http\Controllers\Admin\TicketProblemTypeController::class, 'edit'])->name('admin.tickets.problem-types.edit');
        Route::put('tickets/problem-types/{ticket_problem_type}', [App\Http\Controllers\Admin\TicketProblemTypeController::class, 'update'])->name('admin.tickets.problem-types.update');
        Route::delete('tickets/problem-types/{ticket_problem_type}', [App\Http\Controllers\Admin\TicketProblemTypeController::class, 'destroy'])->name('admin.tickets.problem-types.destroy');
        Route::get('tickets/{ticket_report}', [App\Http\Controllers\Admin\TicketReportController::class, 'show'])->name('admin.tickets.show');
        Route::patch('tickets/{ticket_report}', [App\Http\Controllers\Admin\TicketReportController::class, 'update'])->name('admin.tickets.update');
        Route::post('tickets/{ticket_report}/notes', [App\Http\Controllers\Admin\TicketReportController::class, 'storeNote'])->name('admin.tickets.notes.store');
        Route::delete('tickets/{ticket_report}/notes/{note}', [App\Http\Controllers\Admin\TicketReportController::class, 'destroyNote'])->name('admin.tickets.notes.destroy');

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
            Route::get('linked-accounts', [App\Http\Controllers\Admin\LinkedAccountController::class, 'index'])->name('admin.linked-accounts.index');
        });
        Route::middleware(['admin.subfeature:subscriptions,starlinks'])->group(function () {
        Route::get('starlinks/import', [App\Http\Controllers\Admin\StarlinkController::class, 'importForm'])->name('admin.starlinks.import');
        Route::get('starlinks/import/template', [App\Http\Controllers\Admin\StarlinkController::class, 'importTemplate'])->name('admin.starlinks.import.template');
        Route::get('starlinks/export/csv', [App\Http\Controllers\Admin\StarlinkController::class, 'exportCsv'])->name('admin.starlinks.export.csv');
        Route::get('starlinks/export/pdf', [App\Http\Controllers\Admin\StarlinkController::class, 'exportPdf'])->name('admin.starlinks.export.pdf');
        Route::post('starlinks/import', [App\Http\Controllers\Admin\StarlinkController::class, 'processImport'])->name('admin.starlinks.import.process');
        Route::resource('starlinks', App\Http\Controllers\Admin\StarlinkController::class)->names('admin.starlinks');
        });
        Route::middleware(['admin.subfeature:subscriptions,omadas'])->group(function () {
        Route::get('omadas/import', [App\Http\Controllers\Admin\OmadaController::class, 'importForm'])->name('admin.omadas.import');
        Route::get('omadas/import/template', [App\Http\Controllers\Admin\OmadaController::class, 'importTemplate'])->name('admin.omadas.import.template');
        Route::post('omadas/import', [App\Http\Controllers\Admin\OmadaController::class, 'processImport'])->name('admin.omadas.import.process');
        Route::resource('omadas', App\Http\Controllers\Admin\OmadaController::class)->names('admin.omadas');
        });
        Route::middleware(['admin.subfeature:subscriptions,plan_types'])->group(function () {
            Route::resource('subscription-plan-types', App\Http\Controllers\Admin\SubscriptionPlanTypeController::class)->names('admin.subscription-plan-types');
        });
    });

    // System Management (landing page, stacks — parent system permission)
    Route::middleware(['admin.permission:system'])->group(function () {
        Route::middleware(['admin.subfeature:system,landing_page'])->group(function () {
            Route::get('/landing-page', [\App\Http\Controllers\Admin\LandingPageController::class, 'index'])->name('admin.landing-page.index');
            Route::post('/landing-page', [\App\Http\Controllers\Admin\LandingPageController::class, 'update'])->name('admin.landing-page.update');
        });

        Route::middleware(['admin.subfeature:system,stacks'])->group(function () {
            Route::resource('stacks', \App\Http\Controllers\Admin\StackController::class)->names('admin.stacks');
        });

        // Status Management
        Route::get('status/online-users', [StatusController::class, 'getOnlineUsers'])->name('status.online-users');
        Route::get('status/away-users', [StatusController::class, 'getAwayUsers'])->name('status.away-users');
        Route::get('status/idle-users', [StatusController::class, 'getIdleUsers'])->name('status.idle-users');
        Route::get('status/all-users', [StatusController::class, 'getAllUserStatuses'])->name('status.all-users');
    });

    // Feedback Management – requires feedback permission (or Communication → Feedback via canAccessCommunicationFeature)
    Route::middleware(['admin.permission:feedback'])->group(function () {
        Route::resource('feedback', App\Http\Controllers\Admin\FeedbackController::class)->names('admin.feedback');
        Route::post('feedback/{feedback}/assign', [App\Http\Controllers\Admin\FeedbackController::class, 'assign'])->name('admin.feedback.assign');
        Route::get('feedback-stats', [App\Http\Controllers\Admin\FeedbackController::class, 'getStats'])->name('admin.feedback.stats');
        Route::get('feedback-admins', [App\Http\Controllers\Admin\FeedbackController::class, 'getAdmins'])->name('admin.feedback.admins');
    });
});

// User Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/access', [\App\Http\Controllers\User\AccountTerminatedController::class, 'show'])
        ->name('user.account-terminated');

    // Teachers only; kept on `auth` alone so access is not coupled to student termination checks.
    Route::get('/teacher/pending-applications', [UserDashboardController::class, 'teacherPendingApplications'])->name('user.teacher.pending-applications');
});

Route::middleware(['auth', 'student.not_terminated'])->group(function () {
    Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('user.dashboard');
    Route::post('/dashboard/rules-regulations/acknowledge', [UserDashboardController::class, 'acknowledgeRulesRegulations'])->name('user.rules-regulations.acknowledge');
    Route::get('/teacher/students', [UserDashboardController::class, 'teacherStudents'])->name('user.teacher.students');
    Route::get('/teacher/news', [UserDashboardController::class, 'teacherNews'])->name('user.teacher.news');
    Route::get('/teacher/excused-requests', [UserDashboardController::class, 'teacherExcusedRequests'])->name('user.teacher.excused-requests.index');
    Route::post('/teacher/excused-requests', [UserDashboardController::class, 'storeTeacherExcusedRequest'])->name('user.teacher.excused-requests.store');
    Route::get('/teacher/moa', [UserTeacherMoaController::class, 'index'])->name('user.teacher.moa.index');
    Route::post('/teacher/moa', [UserTeacherMoaController::class, 'store'])->name('user.teacher.moa.store');
    Route::get('/teacher/moa/preview', [UserTeacherMoaController::class, 'preview'])->name('user.teacher.moa.preview');
    Route::get('/technician/tickets', [App\Http\Controllers\User\TechnicianTicketController::class, 'index'])->name('user.technician-tickets.index');
    Route::patch('/technician/tickets/{ticket}', [App\Http\Controllers\User\TechnicianTicketController::class, 'update'])->name('user.technician-tickets.update');
    // TOR PDF for students
    Route::get('/tor', [UserDashboardController::class, 'tor'])->name('user.tor');
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
    Route::get('/friends', [App\Http\Controllers\FriendshipController::class, 'index'])->name('friends.index');
    Route::get('/friends/search', [App\Http\Controllers\FriendshipController::class, 'search'])->name('friends.search');
    Route::post('/friends/send-request', [App\Http\Controllers\FriendshipController::class, 'sendRequest'])->name('friends.send-request');
    Route::post('/friends/{friendshipId}/accept', [App\Http\Controllers\FriendshipController::class, 'acceptRequest'])->name('friends.accept');
    Route::post('/friends/{friendshipId}/reject', [App\Http\Controllers\FriendshipController::class, 'rejectRequest'])->name('friends.reject');
    Route::post('/friends/{friendshipId}/cancel', [App\Http\Controllers\FriendshipController::class, 'cancelRequest'])->name('friends.cancel');
    Route::post('/friends/{friendId}/remove', [App\Http\Controllers\FriendshipController::class, 'removeFriend'])->name('friends.remove');
    Route::post('/friends/{userId}/block', [App\Http\Controllers\FriendshipController::class, 'blockUser'])->name('friends.block');

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
    Route::post('/profile/password/change', [App\Http\Controllers\User\ProfileController::class, 'changePassword'])->name('profile.password.change');
    Route::delete('/profile/picture', [App\Http\Controllers\User\ProfileController::class, 'removeProfilePicture'])->name('profile.picture.remove');
    Route::delete('/profile/cover', [App\Http\Controllers\User\ProfileController::class, 'removeCoverPhoto'])->name('profile.cover.remove');

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
