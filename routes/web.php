<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\SmtpProviderController;
use App\Http\Controllers\Admin\ContactController;
use App\Http\Controllers\Admin\CampaignController;
use App\Http\Controllers\Admin\EmailLogController;
use App\Http\Controllers\Admin\QueueController;
use App\Http\Controllers\Admin\SingleMailController;
use App\Http\Controllers\Admin\NotificationController;

Route::get('/', function () {
    return redirect()->route('admin.login');
});

// Admin Auth
Route::get('/admin/login', [AdminAuthController::class, 'showLogin'])->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'login'])->name('admin.login.post');
Route::post('/admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

// Dashboard
Route::get('/admin/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

// SMTP Providers
Route::get('/admin/smtp-providers', [SmtpProviderController::class, 'index'])->name('admin.smtp.index');
Route::get('/admin/smtp-providers/create', [SmtpProviderController::class, 'create'])->name('admin.smtp.create');
Route::post('/admin/smtp-providers', [SmtpProviderController::class, 'store'])->name('admin.smtp.store');
Route::get('/admin/smtp-providers/{id}/edit', [SmtpProviderController::class, 'edit'])->name('admin.smtp.edit');
Route::put('/admin/smtp-providers/{id}', [SmtpProviderController::class, 'update'])->name('admin.smtp.update');
Route::delete('/admin/smtp-providers/{id}', [SmtpProviderController::class, 'destroy'])->name('admin.smtp.destroy');
Route::post('/admin/smtp-providers/{id}/test', [SmtpProviderController::class, 'test'])->name('admin.smtp.test');
Route::post('/admin/smtp-providers/{id}/reset-daily', [SmtpProviderController::class, 'resetDailyCount'])->name('admin.smtp.reset-daily');

// Contacts
Route::get('/admin/contacts', [ContactController::class, 'index'])->name('admin.contacts.index');
Route::get('/admin/contacts/create', [ContactController::class, 'create'])->name('admin.contacts.create');
Route::post('/admin/contacts', [ContactController::class, 'store'])->name('admin.contacts.store');
Route::get('/admin/contacts/import', [ContactController::class, 'importForm'])->name('admin.contacts.import');
Route::post('/admin/contacts/import', [ContactController::class, 'import'])->name('admin.contacts.import.post');
Route::delete('/admin/contacts/bulk-delete', [ContactController::class, 'bulkDelete'])->name('admin.contacts.bulk-delete');
Route::get('/admin/contacts/generate', [ContactController::class, 'generateForm'])->name('admin.contacts.generate.form');
Route::post('/admin/contacts/generate', [ContactController::class, 'generate'])->name('admin.contacts.generate.post');
Route::get('/admin/contacts/{id}/edit', [ContactController::class, 'edit'])->name('admin.contacts.edit');
Route::put('/admin/contacts/{id}', [ContactController::class, 'update'])->name('admin.contacts.update');
Route::delete('/admin/contacts/{id}', [ContactController::class, 'destroy'])->name('admin.contacts.destroy');
Route::get('/admin/contacts/export', [ContactController::class, 'export'])->name('admin.contacts.export');

// Campaigns
Route::get('/admin/campaigns', [CampaignController::class, 'index'])->name('admin.campaigns.index');
Route::get('/admin/campaigns/create', [CampaignController::class, 'create'])->name('admin.campaigns.create');
Route::post('/admin/campaigns', [CampaignController::class, 'store'])->name('admin.campaigns.store');
Route::get('/admin/campaigns/{id}', [CampaignController::class, 'show'])->name('admin.campaigns.show');
Route::get('/admin/campaigns/{id}/edit', [CampaignController::class, 'edit'])->name('admin.campaigns.edit');
Route::put('/admin/campaigns/{id}', [CampaignController::class, 'update'])->name('admin.campaigns.update');
Route::delete('/admin/campaigns/{id}', [CampaignController::class, 'destroy'])->name('admin.campaigns.destroy');
Route::post('/admin/campaigns/{id}/send', [CampaignController::class, 'send'])->name('admin.campaigns.send');
Route::post('/admin/campaigns/{id}/pause', [CampaignController::class, 'pause'])->name('admin.campaigns.pause');
Route::post('/admin/campaigns/{id}/resume', [CampaignController::class, 'resume'])->name('admin.campaigns.resume');
Route::post('/admin/campaigns/{id}/cancel', [CampaignController::class, 'cancel'])->name('admin.campaigns.cancel');
Route::post('/admin/campaigns/{id}/recycle', [CampaignController::class, 'recycle'])->name('admin.campaigns.recycle');
Route::post('/admin/campaigns/{id}/clear-queued', [CampaignController::class, 'clearQueued'])->name('admin.campaigns.clearQueued');
Route::post('/admin/campaigns/{id}/duplicate', [CampaignController::class, 'duplicate'])->name('admin.campaigns.duplicate');
Route::get('/admin/campaigns/{id}/reschedule', [CampaignController::class, 'rescheduleForm'])->name('admin.campaigns.reschedule');
Route::post('/admin/campaigns/{id}/reschedule', [CampaignController::class, 'reschedule'])->name('admin.campaigns.reschedule.post');

// Email Logs
Route::get('/admin/email-logs', [EmailLogController::class, 'index'])->name('admin.logs.index');
Route::get('/admin/email-logs/{id}', [EmailLogController::class, 'show'])->name('admin.logs.show');
Route::post('/admin/email-logs/{id}/retry', [EmailLogController::class, 'retry'])->name('admin.logs.retry');
Route::post('/admin/email-logs/clear', [EmailLogController::class, 'clearLogs'])->name('admin.logs.clear');
Route::get('/admin/email-logs/export/csv', [EmailLogController::class, 'exportCsv'])->name('admin.logs.export');
Route::get('/admin/email-logs/export/campaign/{id}', [EmailLogController::class, 'exportByCampaign'])->name('admin.logs.export.campaign');

// Queue Monitor
Route::get('/admin/queue', [QueueController::class, 'index'])->name('admin.queue.index');
Route::post('/admin/queue/process', [QueueController::class, 'processQueue'])->name('admin.queue.process');
Route::post('/admin/queue/clear-failed', [QueueController::class, 'clearFailed'])->name('admin.queue.clear-failed');
Route::post('/admin/queue/delete-by-range', [QueueController::class, 'deleteByRange'])->name('admin.queue.deleteByRange');

// Single Mail Sender
Route::get('/admin/single-mail', [SingleMailController::class, 'index'])->name('admin.single-mail.index');
Route::post('/admin/single-mail/send', [SingleMailController::class, 'send'])->name('admin.single-mail.send');
Route::get('/admin/single-mail/stream/{token}', [SingleMailController::class, 'stream'])->name('admin.single-mail.stream');
Route::get('/admin/single-mail/laravel-log', [SingleMailController::class, 'laravelLog'])->name('admin.single-mail.log');

// Push Notifications
Route::get('/admin/notifications', [NotificationController::class, 'index'])->name('admin.notifications.index');
Route::get('/admin/notifications/feed', [NotificationController::class, 'feed'])->name('admin.notifications.feed');
Route::post('/admin/notifications/{id}/read', [NotificationController::class, 'markRead'])->name('admin.notifications.read');
Route::post('/admin/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('admin.notifications.read-all');
Route::delete('/admin/notifications/{id}', [NotificationController::class, 'destroy'])->name('admin.notifications.destroy');

// Mailing Lists
Route::resource('/admin/mailing-lists', \App\Http\Controllers\Admin\MailingListController::class)->names([
    'index'   => 'admin.mailing_lists.index',
    'create'  => 'admin.mailing_lists.create',
    'store'   => 'admin.mailing_lists.store',
    'edit'    => 'admin.mailing_lists.edit',
    'update'  => 'admin.mailing_lists.update',
    'destroy' => 'admin.mailing_lists.destroy',
]);