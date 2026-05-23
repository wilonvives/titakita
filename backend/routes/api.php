<?php

use TitaKita\Http\Actions\Accounts\CreateAccountAction;
use TitaKita\Http\Actions\Accounts\GetAccountAction;
use TitaKita\Http\Actions\Accounts\Stripe\CreateStripeConnectAccountAction;
use TitaKita\Http\Actions\Accounts\Stripe\GetStripeConnectAccountsAction;
use TitaKita\Http\Actions\Accounts\UpdateAccountAction;
use TitaKita\Http\Actions\Accounts\Vat\GetAccountVatSettingAction;
use TitaKita\Http\Actions\Accounts\Vat\UpsertAccountVatSettingAction;
use TitaKita\Http\Actions\Affiliates\CreateAffiliateAction;
use TitaKita\Http\Actions\Affiliates\DeleteAffiliateAction;
use TitaKita\Http\Actions\Affiliates\ExportAffiliatesAction;
use TitaKita\Http\Actions\Affiliates\GetAffiliateAction;
use TitaKita\Http\Actions\Affiliates\GetAffiliatesAction;
use TitaKita\Http\Actions\Affiliates\UpdateAffiliateAction;
use TitaKita\Http\Actions\Attendees\CheckInAttendeeAction;
use TitaKita\Http\Actions\Attendees\CreateAttendeeAction;
use TitaKita\Http\Actions\Attendees\EditAttendeeAction;
use TitaKita\Http\Actions\Attendees\ExportAttendeesAction;
use TitaKita\Http\Actions\Attendees\GetAttendeeAction;
use TitaKita\Http\Actions\Attendees\GetAttendeeActionPublic;
use TitaKita\Http\Actions\Attendees\GetAttendeesAction;
use TitaKita\Http\Actions\Attendees\PartialEditAttendeeAction;
use TitaKita\Http\Actions\Attendees\ResendAttendeeTicketAction;
use TitaKita\Http\Actions\Auth\AcceptInvitationAction;
use TitaKita\Http\Actions\Auth\ForgotPasswordAction;
use TitaKita\Http\Actions\Auth\GetUserInvitationAction;
use TitaKita\Http\Actions\Auth\LoginAction;
use TitaKita\Http\Actions\Auth\LogoutAction;
use TitaKita\Http\Actions\Auth\RefreshTokenAction;
use TitaKita\Http\Actions\Auth\ResetPasswordAction;
use TitaKita\Http\Actions\Auth\ValidateResetPasswordTokenAction;
use TitaKita\Http\Actions\CapacityAssignments\CreateCapacityAssignmentAction;
use TitaKita\Http\Actions\CapacityAssignments\DeleteCapacityAssignmentAction;
use TitaKita\Http\Actions\CapacityAssignments\GetCapacityAssignmentAction;
use TitaKita\Http\Actions\CapacityAssignments\GetCapacityAssignmentsAction;
use TitaKita\Http\Actions\CapacityAssignments\UpdateCapacityAssignmentAction;
use TitaKita\Http\Actions\CheckInLists\CreateCheckInListAction;
use TitaKita\Http\Actions\CheckInLists\DeleteCheckInListAction;
use TitaKita\Http\Actions\CheckInLists\GetCheckInListAction;
use TitaKita\Http\Actions\CheckInLists\GetCheckInListsAction;
use TitaKita\Http\Actions\CheckInLists\Public\CreateAttendeeCheckInPublicAction;
use TitaKita\Http\Actions\CheckInLists\Public\DeleteAttendeeCheckInPublicAction;
use TitaKita\Http\Actions\CheckInLists\Public\GetCheckInListAttendeePublicAction;
use TitaKita\Http\Actions\CheckInLists\Public\GetCheckInListAttendeesPublicAction;
use TitaKita\Http\Actions\CheckInLists\Public\GetCheckInListPublicAction;
use TitaKita\Http\Actions\CheckInLists\UpdateCheckInListAction;
use TitaKita\Http\Actions\Common\GetColorThemesAction;
use TitaKita\Http\Actions\Common\Webhooks\StripeIncomingWebhookAction;
use TitaKita\Http\Actions\Events\CreateEventAction;
use TitaKita\Http\Actions\Events\DuplicateEventAction;
use TitaKita\Http\Actions\Events\GetEventAction;
use TitaKita\Http\Actions\Events\GetEventPublicAction;
use TitaKita\Http\Actions\Events\GetEventsAction;
use TitaKita\Http\Actions\Events\GetOrganizerEventsPublicAction;
use TitaKita\Http\Actions\Events\Images\CreateEventImageAction;
use TitaKita\Http\Actions\Events\Images\DeleteEventImageAction;
use TitaKita\Http\Actions\Events\Images\GetEventImagesAction;
use TitaKita\Http\Actions\Events\Stats\GetEventStatsAction;
use TitaKita\Http\Actions\Events\UpdateEventAction;
use TitaKita\Http\Actions\Events\DeleteEventAction;
use TitaKita\Http\Actions\Events\GetEventDeletionStatusAction;
use TitaKita\Http\Actions\Events\UpdateEventStatusAction;
use TitaKita\Http\Actions\EventSettings\EditEventSettingsAction;
use TitaKita\Http\Actions\EventSettings\GetEventSettingsAction;
use TitaKita\Http\Actions\EventSettings\GetPlatformFeePreviewAction;
use TitaKita\Http\Actions\EmailTemplates\CreateOrganizerEmailTemplateAction;
use TitaKita\Http\Actions\EmailTemplates\CreateEventEmailTemplateAction;
use TitaKita\Http\Actions\EmailTemplates\UpdateOrganizerEmailTemplateAction;
use TitaKita\Http\Actions\EmailTemplates\UpdateEventEmailTemplateAction;
use TitaKita\Http\Actions\EmailTemplates\GetOrganizerEmailTemplatesAction;
use TitaKita\Http\Actions\EmailTemplates\GetEventEmailTemplatesAction;
use TitaKita\Http\Actions\EmailTemplates\DeleteOrganizerEmailTemplateAction;
use TitaKita\Http\Actions\EmailTemplates\DeleteEventEmailTemplateAction;
use TitaKita\Http\Actions\EmailTemplates\PreviewOrganizerEmailTemplateAction;
use TitaKita\Http\Actions\EmailTemplates\PreviewEventEmailTemplateAction;
use TitaKita\Http\Actions\EmailTemplates\GetAvailableTokensAction;
use TitaKita\Http\Actions\EmailTemplates\GetDefaultEmailTemplateAction;
use TitaKita\Http\Actions\EventSettings\PartialEditEventSettingsAction;
use TitaKita\Http\Actions\Images\CreateImageAction;
use TitaKita\Http\Actions\Images\DeleteImageAction;
use TitaKita\Http\Actions\Messages\CancelMessageAction;
use TitaKita\Http\Actions\Messages\GetMessageRecipientsAction;
use TitaKita\Http\Actions\Messages\GetMessagesAction;
use TitaKita\Http\Actions\Messages\SendMessageAction;
use TitaKita\Http\Actions\Orders\CancelOrderAction;
use TitaKita\Http\Actions\Orders\DownloadOrderInvoiceAction;
use TitaKita\Http\Actions\Orders\EditOrderAction;
use TitaKita\Http\Actions\Orders\ExportOrdersAction;
use TitaKita\Http\Actions\Orders\GetOrderAction;
use TitaKita\Http\Actions\Orders\GetOrdersAction;
use TitaKita\Http\Actions\Orders\MarkOrderAsPaidAction;
use TitaKita\Http\Actions\Orders\MessageOrderAction;
use TitaKita\Http\Actions\Orders\Payment\RefundOrderAction;
use TitaKita\Http\Actions\Orders\Payment\Stripe\CreatePaymentIntentActionPublic;
use TitaKita\Http\Actions\Orders\Payment\Stripe\GetPaymentIntentActionPublic;
use TitaKita\Http\Actions\Orders\Public\AbandonOrderActionPublic;
use TitaKita\Http\Actions\Orders\Public\CompleteOrderActionPublic;
use TitaKita\Http\Actions\Orders\Public\CreateOrderActionPublic;
use TitaKita\Http\Actions\Orders\Public\DownloadOrderInvoicePublicAction;
use TitaKita\Http\Actions\Orders\Public\GetOrderActionPublic;
use TitaKita\Http\Actions\Orders\Public\TransitionOrderToOfflinePaymentPublicAction;
use TitaKita\Http\Actions\Orders\ResendOrderConfirmationAction;
use TitaKita\Http\Actions\Organizers\CreateOrganizerAction;
use TitaKita\Http\Actions\SelfService\EditAttendeePublicAction;
use TitaKita\Http\Actions\SelfService\EditOrderPublicAction;
use TitaKita\Http\Actions\SelfService\ResendAttendeeTicketPublicAction;
use TitaKita\Http\Actions\SelfService\ResendOrderConfirmationPublicAction;
use TitaKita\Http\Actions\Organizers\EditOrganizerAction;
use TitaKita\Http\Actions\Organizers\GetOrganizerAction;
use TitaKita\Http\Actions\Organizers\GetOrganizerEventsAction;
use TitaKita\Http\Actions\Organizers\GetOrganizersAction;
use TitaKita\Http\Actions\Organizers\GetPublicOrganizerAction;
use TitaKita\Http\Actions\Organizers\Orders\GetOrganizerOrdersAction;
use TitaKita\Http\Actions\Organizers\Public\SendOrganizerContactMessagePublicAction;
use TitaKita\Http\Actions\Organizers\Settings\GetOrganizerSettingsAction;
use TitaKita\Http\Actions\Organizers\Settings\PartialUpdateOrganizerSettingsAction;
use TitaKita\Http\Actions\Organizers\Stats\GetOrganizerStatsAction;
use TitaKita\Http\Actions\Organizers\DeleteOrganizerAction;
use TitaKita\Http\Actions\Organizers\GetOrganizerDeletionStatusAction;
use TitaKita\Http\Actions\Organizers\UpdateOrganizerStatusAction;
use TitaKita\Http\Actions\Organizers\Webhooks\CreateOrganizerWebhookAction;
use TitaKita\Http\Actions\Organizers\Webhooks\DeleteOrganizerWebhookAction;
use TitaKita\Http\Actions\Organizers\Webhooks\EditOrganizerWebhookAction;
use TitaKita\Http\Actions\Organizers\Webhooks\GetOrganizerWebhookAction;
use TitaKita\Http\Actions\Organizers\Webhooks\GetOrganizerWebhookLogsAction;
use TitaKita\Http\Actions\Organizers\Webhooks\GetOrganizerWebhooksAction;
use TitaKita\Http\Actions\ProductCategories\CreateProductCategoryAction;
use TitaKita\Http\Actions\ProductCategories\DeleteProductCategoryAction;
use TitaKita\Http\Actions\ProductCategories\EditProductCategoryAction;
use TitaKita\Http\Actions\ProductCategories\GetProductCategoriesAction;
use TitaKita\Http\Actions\ProductCategories\GetProductCategoryAction;
use TitaKita\Http\Actions\Products\CreateProductAction;
use TitaKita\Http\Actions\Products\DeleteProductAction;
use TitaKita\Http\Actions\Products\EditProductAction;
use TitaKita\Http\Actions\Products\GetProductAction;
use TitaKita\Http\Actions\Products\GetProductsAction;
use TitaKita\Http\Actions\Products\SortProductsAction;
use TitaKita\Http\Actions\PromoCodes\CreatePromoCodeAction;
use TitaKita\Http\Actions\PromoCodes\DeletePromoCodeAction;
use TitaKita\Http\Actions\PromoCodes\GetPromoCodeAction;
use TitaKita\Http\Actions\PromoCodes\GetPromoCodePublic;
use TitaKita\Http\Actions\PromoCodes\GetPromoCodesAction;
use TitaKita\Http\Actions\PromoCodes\UpdatePromoCodeAction;
use TitaKita\Http\Actions\Questions\CreateQuestionAction;
use TitaKita\Http\Actions\Questions\DeleteQuestionAction;
use TitaKita\Http\Actions\Questions\EditQuestionAction;
use TitaKita\Http\Actions\Questions\EditQuestionAnswerAction;
use TitaKita\Http\Actions\Questions\ExportQuestionAnswersAction;
use TitaKita\Http\Actions\Questions\GetQuestionAction;
use TitaKita\Http\Actions\Questions\GetQuestionsAction;
use TitaKita\Http\Actions\Questions\GetQuestionsPublicAction;
use TitaKita\Http\Actions\Questions\SortQuestionsAction;
use TitaKita\Http\Actions\Reports\ExportOrganizerReportAction;
use TitaKita\Http\Actions\Reports\GetOrganizerReportAction;
use TitaKita\Http\Actions\Reports\GetReportAction;
use TitaKita\Http\Actions\TitaKita\GetNodeDescriptorAction;
use TitaKita\Http\Actions\TitaKita\GetPublicEventsFeedAction;
use TitaKita\Http\Actions\Sitemap\GetSitemapEventsAction;
use TitaKita\Http\Actions\Sitemap\GetSitemapIndexAction;
use TitaKita\Http\Actions\Sitemap\GetSitemapOrganizersAction;
use TitaKita\Http\Actions\TaxesAndFees\CreateTaxOrFeeAction;
use TitaKita\Http\Actions\TaxesAndFees\DeleteTaxOrFeeAction;
use TitaKita\Http\Actions\TaxesAndFees\EditTaxOrFeeAction;
use TitaKita\Http\Actions\TaxesAndFees\GetTaxOrFeeAction;
use TitaKita\Http\Actions\Users\CancelEmailChangeAction;
use TitaKita\Http\Actions\Users\ConfirmEmailAddressAction;
use TitaKita\Http\Actions\Users\ConfirmEmailChangeAction;
use TitaKita\Http\Actions\Users\ConfirmEmailWithCodeAction;
use TitaKita\Http\Actions\Users\CreateUserAction;
use TitaKita\Http\Actions\Users\DeleteInvitationAction;
use TitaKita\Http\Actions\Users\GetMeAction;
use TitaKita\Http\Actions\Users\GetUserAction;
use TitaKita\Http\Actions\Users\GetUsersAction;
use TitaKita\Http\Actions\Users\ResendEmailConfirmationAction;
use TitaKita\Http\Actions\Users\ResendInvitationAction;
use TitaKita\Http\Actions\Users\UpdateMeAction;
use TitaKita\Http\Actions\Users\UpdateUserAction;
use TitaKita\Http\Actions\Admin\Accounts\AssignConfigurationAction;
use TitaKita\Http\Actions\Admin\Accounts\GetAccountAction as GetAdminAccountAction;
use TitaKita\Http\Actions\Admin\Accounts\GetAllAccountsAction as GetAllAdminAccountsAction;
use TitaKita\Http\Actions\Admin\Accounts\UpdateAccountVatSettingAction as UpdateAdminAccountVatSettingAction;
use TitaKita\Http\Actions\Admin\Configurations\CreateConfigurationAction;
use TitaKita\Http\Actions\Admin\Configurations\DeleteConfigurationAction;
use TitaKita\Http\Actions\Admin\Configurations\GetAllConfigurationsAction;
use TitaKita\Http\Actions\Admin\Configurations\UpdateConfigurationAction;
use TitaKita\Http\Actions\Admin\Events\GetAllEventsAction as GetAllAdminEventsAction;
use TitaKita\Http\Actions\Admin\Events\GetUpcomingEventsAction;
use TitaKita\Http\Actions\Admin\FailedJobs\DeleteAllFailedJobsAction;
use TitaKita\Http\Actions\Admin\FailedJobs\DeleteFailedJobAction;
use TitaKita\Http\Actions\Admin\FailedJobs\GetAllFailedJobsAction;
use TitaKita\Http\Actions\Admin\FailedJobs\RetryAllFailedJobsAction;
use TitaKita\Http\Actions\Admin\FailedJobs\RetryFailedJobAction;
use TitaKita\Http\Actions\Admin\Messages\ApproveMessageAction;
use TitaKita\Http\Actions\Admin\Messages\GetAllMessagesAction as GetAllAdminMessagesAction;
use TitaKita\Http\Actions\Admin\GetMessagingTiersAction;
use TitaKita\Http\Actions\Admin\Accounts\UpdateAccountMessagingTierAction;
use TitaKita\Http\Actions\Admin\Orders\GetAllOrdersAction;
use TitaKita\Http\Actions\Admin\Attribution\GetUtmAttributionStatsAction;
use TitaKita\Http\Actions\Admin\GetSystemInfoAction;
use TitaKita\Http\Actions\Admin\Stats\GetAdminDashboardDataAction;
use TitaKita\Http\Actions\Admin\Stats\GetAdminStatsAction;
use TitaKita\Http\Actions\Admin\Users\GetAllUsersAction;
use TitaKita\Http\Actions\Admin\Users\StartImpersonationAction;
use TitaKita\Http\Actions\Admin\Users\StopImpersonationAction;
use TitaKita\Http\Actions\TicketLookup\GetOrdersByLookupTokenAction;
use TitaKita\Http\Actions\TicketLookup\SendTicketLookupEmailAction;
use TitaKita\Http\Actions\Waitlist\Organizer\CancelWaitlistEntryAction;
use TitaKita\Http\Actions\Waitlist\Organizer\GetWaitlistEntriesAction;
use TitaKita\Http\Actions\Waitlist\Organizer\GetWaitlistStatsAction;
use TitaKita\Http\Actions\Waitlist\Organizer\OfferWaitlistEntryAction;
use TitaKita\Http\Actions\Waitlist\Public\CancelWaitlistEntryActionPublic;
use TitaKita\Http\Actions\Waitlist\Public\CreateWaitlistEntryActionPublic;
use TitaKita\Http\Actions\Webhooks\CreateWebhookAction;
use TitaKita\Http\Actions\Webhooks\DeleteWebhookAction;
use TitaKita\Http\Actions\Webhooks\EditWebhookAction;
use TitaKita\Http\Actions\Webhooks\GetWebhookAction;
use TitaKita\Http\Actions\Webhooks\GetWebhookLogsAction;
use TitaKita\Http\Actions\Webhooks\GetWebhooksAction;
use Illuminate\Routing\Router;

/** @var Router|Router $router */
$router = app()->get('router');

$router->prefix('/auth')->group(
    function (Router $router): void {
        // Auth
        $router->post('/login', LoginAction::class)->name('auth.login');
        $router->post('/logout', LogoutAction::class)->name('auth.logout');
        $router->post('/register', CreateAccountAction::class)->name('auth.register');
        $router->post('/forgot-password', ForgotPasswordAction::class)->name('auth.forgot-password');

        // Invitations
        $router->get('/invitation/{invite_token}', GetUserInvitationAction::class)->name('auth.invitation');
        $router->post('/invitation/{invite_token}', AcceptInvitationAction::class)->name('auth.accept-invitation');

        // Reset Passwords
        $router->get('/reset-password/{reset_token}', ValidateResetPasswordTokenAction::class)->name('auth.validate-reset-password-token');
        $router->post('/reset-password/{reset_token}', ResetPasswordAction::class)->name('auth.reset-password');
    }
);

/**
 * Logged In Routes
 */
$router->middleware(['auth:api'])->group(
    function (Router $router): void {
        // Auth
        $router->get('/auth/logout', LogoutAction::class);
        $router->post('/auth/refresh', RefreshTokenAction::class);

        // Users
        $router->get('/users/me', GetMeAction::class);
        $router->put('/users/me', UpdateMeAction::class);
        $router->post('/users', CreateUserAction::class);
        $router->get('/users', GetUsersAction::class);
        $router->get('/users/{user_id}', GetUserAction::class);
        $router->put('/users/{user_id}', UpdateUserAction::class);
        $router->post('/users/{user_id}/email-change/{changeToken}', ConfirmEmailChangeAction::class);
        $router->post('/users/{user_id}/invitation', ResendInvitationAction::class);
        $router->delete('/users/{user_id}/invitation', DeleteInvitationAction::class);
        $router->delete('/users/{user_id}/email-change', CancelEmailChangeAction::class);
        $router->post('/users/{user_id}/confirm-email/{resetToken}', ConfirmEmailAddressAction::class);
        $router->post('/users/{user_id}/resend-email-confirmation', ResendEmailConfirmationAction::class);
        $router->post('/users/{user_id}/confirm-email-with-code', ConfirmEmailWithCodeAction::class);

        // Accounts
        $router->get('/accounts/{account_id?}', GetAccountAction::class);
        $router->put('/accounts/{account_id?}', UpdateAccountAction::class);
        $router->get('/accounts/{account_id}/stripe/connect_accounts', GetStripeConnectAccountsAction::class);
        $router->post('/accounts/{account_id}/stripe/connect', CreateStripeConnectAccountAction::class);

        // VAT Settings
        $router->get('/accounts/{account_id}/vat-settings', GetAccountVatSettingAction::class);
        $router->post('/accounts/{account_id}/vat-settings', UpsertAccountVatSettingAction::class);

        // Organizers
        $router->post('/organizers', CreateOrganizerAction::class);
        // This is POST instead of PUT because you can't upload files via PUT in PHP (at least not easily)
        $router->post('/organizers/{organizer_id}', EditOrganizerAction::class);
        $router->put('/organizers/{organizer_id}/status', UpdateOrganizerStatusAction::class);
        $router->delete('/organizers/{organizer_id}', DeleteOrganizerAction::class);
        $router->get('/organizers/{organizer_id}/deletion-status', GetOrganizerDeletionStatusAction::class);
        $router->get('/organizers', GetOrganizersAction::class);
        $router->get('/organizers/{organizer_id}', GetOrganizerAction::class);
        $router->get('/organizers/{organizer_id}/events', GetOrganizerEventsAction::class);
        $router->get('/organizers/{organizer_id}/stats', GetOrganizerStatsAction::class);
        $router->get('/organizers/{organizer_id}/orders', GetOrganizerOrdersAction::class);
        $router->get('/organizers/{organizer_id}/settings', GetOrganizerSettingsAction::class);
        $router->patch('/organizers/{organizer_id}/settings', PartialUpdateOrganizerSettingsAction::class);
        $router->get('/organizers/{organizer_id}/reports/{report_type}', GetOrganizerReportAction::class);
        $router->get('/organizers/{organizer_id}/reports/{report_type}/export', ExportOrganizerReportAction::class);
        $router->post('/organizers/{organizer_id}/webhooks', CreateOrganizerWebhookAction::class);
        $router->get('/organizers/{organizer_id}/webhooks', GetOrganizerWebhooksAction::class);
        $router->put('/organizers/{organizer_id}/webhooks/{webhook_id}', EditOrganizerWebhookAction::class);
        $router->get('/organizers/{organizer_id}/webhooks/{webhook_id}', GetOrganizerWebhookAction::class);
        $router->delete('/organizers/{organizer_id}/webhooks/{webhook_id}', DeleteOrganizerWebhookAction::class);
        $router->get('/organizers/{organizer_id}/webhooks/{webhook_id}/logs', GetOrganizerWebhookLogsAction::class);

        // Email Templates - Organizer level
        $router->get('/organizers/{organizerId}/email-templates', GetOrganizerEmailTemplatesAction::class);
        $router->get('/email-templates/defaults', GetDefaultEmailTemplateAction::class);
        $router->post('/organizers/{organizerId}/email-templates', CreateOrganizerEmailTemplateAction::class);
        $router->put('/organizers/{organizerId}/email-templates/{templateId}', UpdateOrganizerEmailTemplateAction::class);
        $router->delete('/organizers/{organizerId}/email-templates/{templateId}', DeleteOrganizerEmailTemplateAction::class);
        $router->post('/organizers/{organizerId}/email-templates/preview', PreviewOrganizerEmailTemplateAction::class);
        $router->get('/email-templates/tokens/{templateType}', GetAvailableTokensAction::class);

        // Taxes and Fees
        $router->post('/accounts/{account_id}/taxes-and-fees', CreateTaxOrFeeAction::class);
        $router->get('/accounts/{account_id}/taxes-and-fees', GetTaxOrFeeAction::class);
        $router->put('/accounts/{account_id}/taxes-and-fees/{tax_or_fee_id}', EditTaxOrFeeAction::class);
        $router->delete('/accounts/{account_id}/taxes-and-fees/{tax_or_fee_id}', DeleteTaxOrFeeAction::class);

        // Events
        $router->post('/events', CreateEventAction::class);
        $router->get('/events', GetEventsAction::class);
        $router->get('/events/{event_id}', GetEventAction::class);
        $router->put('/events/{event_id}', UpdateEventAction::class);
        $router->put('/events/{event_id}/status', UpdateEventStatusAction::class);
        $router->delete('/events/{event_id}', DeleteEventAction::class);
        $router->get('/events/{event_id}/deletion-status', GetEventDeletionStatusAction::class);
        $router->post('/events/{event_id}/duplicate', DuplicateEventAction::class);

        // Product Categories
        $router->post('/events/{event_id}/product-categories', CreateProductCategoryAction::class);
        $router->get('/events/{event_id}/product-categories', GetProductCategoriesAction::class);
        $router->get('/events/{event_id}/product-categories/{category_id}', GetProductCategoryAction::class);
        $router->put('/events/{event_id}/product-categories/{category_id}', EditProductCategoryAction::class);
        $router->delete('/events/{event_id}/product-categories/{category_id}', DeleteProductCategoryAction::class);

        // Products
        $router->post('/events/{event_id}/products', CreateProductAction::class);
        $router->post('/events/{event_id}/products/sort', SortProductsAction::class);
        $router->put('/events/{event_id}/products/{ticket_id}', EditProductAction::class);
        $router->get('/events/{event_id}/products/{ticket_id}', GetProductAction::class);
        $router->delete('/events/{event_id}/products/{ticket_id}', DeleteProductAction::class);
        $router->get('/events/{event_id}/products', GetProductsAction::class);

        // Stats
        $router->get('/events/{event_id}/stats', GetEventStatsAction::class);

        // Email Templates - Event level
        $router->get('/events/{eventId}/email-templates', GetEventEmailTemplatesAction::class);
        $router->post('/events/{eventId}/email-templates', CreateEventEmailTemplateAction::class);
        $router->put('/events/{eventId}/email-templates/{templateId}', UpdateEventEmailTemplateAction::class);
        $router->delete('/events/{eventId}/email-templates/{templateId}', DeleteEventEmailTemplateAction::class);
        $router->post('/events/{eventId}/email-templates/preview', PreviewEventEmailTemplateAction::class);

        // Attendees
        $router->post('/events/{event_id}/attendees', CreateAttendeeAction::class);
        $router->get('/events/{event_id}/attendees', GetAttendeesAction::class);
        $router->get('/events/{event_id}/attendees/{attendee_id}', GetAttendeeAction::class);
        $router->put('/events/{event_id}/attendees/{attendee_id}', EditAttendeeAction::class);
        $router->patch('/events/{event_id}/attendees/{attendee_id}', PartialEditAttendeeAction::class);
        $router->post('/events/{event_id}/attendees/export', ExportAttendeesAction::class);
        $router->post('/events/{event_id}/attendees/{attendee_public_id}/resend-ticket', ResendAttendeeTicketAction::class);
        $router->post('/events/{event_id}/attendees/{attendee_public_id}/check_in', CheckInAttendeeAction::class);

        // Orders
        $router->get('/events/{event_id}/orders', GetOrdersAction::class);
        $router->get('/events/{event_id}/orders/{order_id}', GetOrderAction::class);
        $router->put('/events/{event_id}/orders/{order_id}', EditOrderAction::class);
        $router->post('/events/{event_id}/orders/{order_id}/message', MessageOrderAction::class);
        $router->post('/events/{event_id}/orders/{order_id}/refund', RefundOrderAction::class);
        $router->post('/events/{event_id}/orders/{order_id}/resend_confirmation', ResendOrderConfirmationAction::class);
        $router->post('/events/{event_id}/orders/{order_id}/cancel', CancelOrderAction::class);
        $router->post('/events/{event_id}/orders/{order_id}/mark-as-paid', MarkOrderAsPaidAction::class);
        $router->post('/events/{event_id}/orders/export', ExportOrdersAction::class);
        $router->get('/events/{event_id}/orders/{order_id}/invoice', DownloadOrderInvoiceAction::class);

        // Questions
        $router->post('/events/{event_id}/questions', CreateQuestionAction::class);
        $router->put('/events/{event_id}/questions/{question_id}', EditQuestionAction::class);
        $router->get('/events/{event_id}/questions/{question_id}', GetQuestionAction::class);
        $router->delete('/events/{event_id}/questions/{question_id}', DeleteQuestionAction::class);
        $router->get('/events/{event_id}/questions', GetQuestionsAction::class);
        $router->post('/events/{event_id}/questions/export', ExportOrdersAction::class);
        $router->post('/events/{event_id}/questions/sort', SortQuestionsAction::class);
        $router->put('/events/{event_id}/questions/{question_id}/answers/{answer_id}', EditQuestionAnswerAction::class);
        $router->match(['get', 'post'], '/events/{event_id}/questions/answers/export', ExportQuestionAnswersAction::class);

        // Images
        $router->post('/events/{event_id}/images', CreateEventImageAction::class);
        $router->get('/events/{event_id}/images', GetEventImagesAction::class);
        $router->delete('/events/{event_id}/images/{image_id}', DeleteEventImageAction::class);

        // Promo Codes
        $router->post('/events/{event_id}/promo-codes', CreatePromoCodeAction::class);
        $router->put('/events/{event_id}/promo-codes/{promo_code_id}', UpdatePromoCodeAction::class);
        $router->get('/events/{event_id}/promo-codes', GetPromoCodesAction::class);
        $router->get('/events/{event_id}/promo-codes/{promo_code_id}', GetPromoCodeAction::class);
        $router->delete('/events/{event_id}/promo-codes/{promo_code_id}', DeletePromoCodeAction::class);

        // Affiliates
        $router->post('/events/{event_id}/affiliates', CreateAffiliateAction::class);
        $router->put('/events/{event_id}/affiliates/{affiliate_id}', UpdateAffiliateAction::class);
        $router->get('/events/{event_id}/affiliates', GetAffiliatesAction::class);
        $router->get('/events/{event_id}/affiliates/{affiliate_id}', GetAffiliateAction::class);
        $router->delete('/events/{event_id}/affiliates/{affiliate_id}', DeleteAffiliateAction::class);
        $router->post('/events/{event_id}/affiliates/export', ExportAffiliatesAction::class);

        // Messages
        $router->post('/events/{event_id}/messages', SendMessageAction::class);
        $router->get('/events/{event_id}/messages', GetMessagesAction::class);
        $router->post('/events/{event_id}/messages/{message_id}/cancel', CancelMessageAction::class);
        $router->get('/events/{event_id}/messages/{message_id}/recipients', GetMessageRecipientsAction::class);

        // Event Settings
        $router->get('/events/{event_id}/settings', GetEventSettingsAction::class);
        $router->put('/events/{event_id}/settings', EditEventSettingsAction::class);
        $router->patch('/events/{event_id}/settings', PartialEditEventSettingsAction::class);
        $router->get('/events/{event_id}/settings/platform-fee-preview', GetPlatformFeePreviewAction::class);

        // Capacity Assignments
        $router->post('/events/{event_id}/capacity-assignments', CreateCapacityAssignmentAction::class);
        $router->get('/events/{event_id}/capacity-assignments', GetCapacityAssignmentsAction::class);
        $router->get('/events/{event_id}/capacity-assignments/{capacity_assignment_id}', GetCapacityAssignmentAction::class);
        $router->put('/events/{event_id}/capacity-assignments/{capacity_assignment_id}', UpdateCapacityAssignmentAction::class);
        $router->delete('/events/{event_id}/capacity-assignments/{capacity_assignment_id}', DeleteCapacityAssignmentAction::class);

        // Check-In Lists
        $router->post('/events/{event_id}/check-in-lists', CreateCheckInListAction::class);
        $router->get('/events/{event_id}/check-in-lists', GetCheckInListsAction::class);
        $router->get('/events/{event_id}/check-in-lists/{check_in_list_id}', GetCheckInListAction::class);
        $router->put('/events/{event_id}/check-in-lists/{check_in_list_id}', UpdateCheckInListAction::class);
        $router->delete('/events/{event_id}/check-in-lists/{check_in_list_id}', DeleteCheckInListAction::class);

        // Webhooks
        $router->post('/events/{event_id}/webhooks', CreateWebhookAction::class);
        $router->get('/events/{event_id}/webhooks', GetWebhooksAction::class);
        $router->put('/events/{event_id}/webhooks/{webhook_id}', EditWebhookAction::class);
        $router->get('/events/{event_id}/webhooks/{webhook_id}', GetWebhookAction::class);
        $router->delete('/events/{event_id}/webhooks/{webhook_id}', DeleteWebhookAction::class);
        $router->get('/events/{event_id}/webhooks/{webhook_id}/logs', GetWebhookLogsAction::class);

        // Reports
        $router->get('/events/{event_id}/reports/{report_type}', GetReportAction::class);

        // Waitlist
        $router->get('/events/{event_id}/waitlist', GetWaitlistEntriesAction::class);
        $router->get('/events/{event_id}/waitlist/stats', GetWaitlistStatsAction::class);
        $router->post('/events/{event_id}/waitlist/offer-next', OfferWaitlistEntryAction::class);
        $router->delete('/events/{event_id}/waitlist/{entry_id}', CancelWaitlistEntryAction::class);

        // Images
        $router->post('/images', CreateImageAction::class);
        $router->delete('/images/{image_id}', DeleteImageAction::class);
    }
);

$router->prefix('/admin')->middleware(['auth:api'])->group(
    function (Router $router): void {
        $router->get('/stats', GetAdminStatsAction::class);
        $router->get('/dashboard', GetAdminDashboardDataAction::class);
        $router->get('/attribution/stats', GetUtmAttributionStatsAction::class);
        $router->get('/accounts', GetAllAdminAccountsAction::class);
        $router->get('/accounts/{account_id}', GetAdminAccountAction::class);
        $router->put('/accounts/{account_id}/vat-settings', UpdateAdminAccountVatSettingAction::class);
        $router->put('/accounts/{account_id}/configuration', AssignConfigurationAction::class);
        $router->get('/configurations', GetAllConfigurationsAction::class);
        $router->post('/configurations', CreateConfigurationAction::class);
        $router->put('/configurations/{configuration_id}', UpdateConfigurationAction::class);
        $router->delete('/configurations/{configuration_id}', DeleteConfigurationAction::class);
        $router->get('/users', GetAllUsersAction::class);
        $router->get('/events', GetAllAdminEventsAction::class);
        $router->get('/events/upcoming', GetUpcomingEventsAction::class);
        $router->get('/orders', GetAllOrdersAction::class);
        $router->post('/impersonate/{user_id}', StartImpersonationAction::class);
        $router->post('/stop-impersonation', StopImpersonationAction::class);

        // Failed Jobs
        $router->get('/failed-jobs', GetAllFailedJobsAction::class);
        $router->delete('/failed-jobs/{jobId}', DeleteFailedJobAction::class);
        $router->delete('/failed-jobs', DeleteAllFailedJobsAction::class);
        $router->post('/failed-jobs/{jobId}/retry', RetryFailedJobAction::class);
        $router->post('/failed-jobs/retry-all', RetryAllFailedJobsAction::class);

        // Messages
        $router->get('/messages', GetAllAdminMessagesAction::class);
        $router->post('/messages/{message_id}/approve', ApproveMessageAction::class);

        // Messaging Tiers
        $router->get('/messaging-tiers', GetMessagingTiersAction::class);
        $router->put('/accounts/{account_id}/messaging-tier', UpdateAccountMessagingTierAction::class);

        // System Info
        $router->get('/system-info', GetSystemInfoAction::class);
    }
);

/**
 * Public routes
 */
$router->prefix('/public')->group(
    function (Router $router): void {
        // Events
        $router->get('/events/{event_id}', GetEventPublicAction::class);

        // Organizers
        $router->get('/organizers/{organizer_id}', GetPublicOrganizerAction::class);
        $router->get('/organizers/{organizer_id}/events', GetOrganizerEventsPublicAction::class);
        $router->post('/organizers/{organizer_id}/contact', SendOrganizerContactMessagePublicAction::class);

        // Products
        $router->get('/events/{event_id}/products', GetEventPublicAction::class);

        // Orders
        $router->post('/events/{event_id}/order', CreateOrderActionPublic::class);
        $router->put('/events/{event_id}/order/{order_short_id}', CompleteOrderActionPublic::class);
        $router->get('/events/{event_id}/order/{order_short_id}', GetOrderActionPublic::class);
        $router->post('/events/{event_id}/order/{order_short_id}/abandon', AbandonOrderActionPublic::class);
        $router->post('/events/{event_id}/order/{order_short_id}/await-offline-payment', TransitionOrderToOfflinePaymentPublicAction::class);
        $router->get('/events/{event_id}/order/{order_short_id}/invoice', DownloadOrderInvoicePublicAction::class);

        // Attendees
        $router->get('/events/{event_id}/attendees/{attendee_short_id}', GetAttendeeActionPublic::class);

        // Waitlist
        $router->post('/events/{event_id}/waitlist', CreateWaitlistEntryActionPublic::class)
            ->middleware('throttle:10,1');
        $router->delete('/events/{event_id}/waitlist/{token}', CancelWaitlistEntryActionPublic::class)
            ->middleware('throttle:10,1');

        // Promo codes
        $router->get('/events/{event_id}/promo-codes/{promo_code}', GetPromoCodePublic::class);

        // Stripe payment gateway
        $router->post('/events/{event_id}/order/{order_short_id}/stripe/payment_intent', CreatePaymentIntentActionPublic::class);
        $router->get('/events/{event_id}/order/{order_short_id}/stripe/payment_intent', GetPaymentIntentActionPublic::class);

        // Questions
        $router->get('/events/{event_id}/questions', GetQuestionsPublicAction::class);

        // Webhooks
        $router->post('/webhooks/stripe', StripeIncomingWebhookAction::class);

        // Check-In
        $router->get('/check-in-lists/{check_in_list_short_id}', GetCheckInListPublicAction::class);
        $router->get('/check-in-lists/{check_in_list_short_id}/attendees', GetCheckInListAttendeesPublicAction::class);
        $router->get('/check-in-lists/{check_in_list_short_id}/attendees/{attendee_public_id}', GetCheckInListAttendeePublicAction::class);
        $router->post('/check-in-lists/{check_in_list_short_id}/check-ins', CreateAttendeeCheckInPublicAction::class);
        $router->delete('/check-in-lists/{check_in_list_short_id}/check-ins/{check_in_short_id}', DeleteAttendeeCheckInPublicAction::class);

        // Color themes
        $router->get('/color-themes', GetColorThemesAction::class);

        // Ticket Lookup
        $router->post('/ticket-lookup', SendTicketLookupEmailAction::class);
        $router->get('/ticket-lookup/{token}', GetOrdersByLookupTokenAction::class);

        // Self-service order and attendee edits
        $router->prefix('/events/{event_id}/order/{order_short_id}')->group(function (Router $router): void {
            $router->patch('/', EditOrderPublicAction::class)->middleware('throttle:self-service-edit');
            $router->post('/resend-confirmation', ResendOrderConfirmationPublicAction::class)->middleware('throttle:self-service-email');

            $router->patch('/attendees/{attendee_short_id}', EditAttendeePublicAction::class)->middleware('throttle:self-service-edit');
            $router->post('/attendees/{attendee_short_id}/resend-ticket', ResendAttendeeTicketPublicAction::class)->middleware('throttle:self-service-email');
        });

        // Sitemap
        $router->get('/sitemap.xml', GetSitemapIndexAction::class);
        $router->get('/sitemap-events-{page}.xml', GetSitemapEventsAction::class)->where('page', '[0-9]+');
        $router->get('/sitemap-organizers-{page}.xml', GetSitemapOrganizersAction::class)->where('page', '[0-9]+');

        // TitaKita directory contract: node descriptor (proxied to /.well-known/titakita.json)
        $router->get('/node', GetNodeDescriptorAction::class);

        // TitaKita directory contract: public, schema.org-aligned events feed
        $router->get('/events', GetPublicEventsFeedAction::class);
    }
);

include_once __DIR__ . '/mail.php';
