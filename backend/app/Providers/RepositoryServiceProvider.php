<?php

declare(strict_types=1);

namespace TitaKita\Providers;

use TitaKita\Repository\Eloquent\AccountAttributionRepository;
use TitaKita\Repository\Eloquent\AccountConfigurationRepository;
use TitaKita\Repository\Eloquent\AccountMessagingTierRepository;
use TitaKita\Repository\Eloquent\AccountRepository;
use TitaKita\Repository\Eloquent\AccountStripePlatformRepository;
use TitaKita\Repository\Eloquent\AccountUserRepository;
use TitaKita\Repository\Eloquent\AccountVatSettingRepository;
use TitaKita\Repository\Eloquent\AffiliateRepository;
use TitaKita\Repository\Eloquent\AttendeeCheckInRepository;
use TitaKita\Repository\Eloquent\AttendeeRepository;
use TitaKita\Repository\Eloquent\CapacityAssignmentRepository;
use TitaKita\Repository\Eloquent\CheckInListRepository;
use TitaKita\Repository\Eloquent\EmailTemplateRepository;
use TitaKita\Repository\Eloquent\EventDailyStatisticRepository;
use TitaKita\Repository\Eloquent\EventRepository;
use TitaKita\Repository\Eloquent\EventSettingsRepository;
use TitaKita\Repository\Eloquent\EventStatisticRepository;
use TitaKita\Repository\Eloquent\ImageRepository;
use TitaKita\Repository\Eloquent\InvoiceRepository;
use TitaKita\Repository\Eloquent\MessageRepository;
use TitaKita\Repository\Eloquent\OrderApplicationFeeRepository;
use TitaKita\Repository\Eloquent\OrderAuditLogRepository;
use TitaKita\Repository\Eloquent\OrderItemRepository;
use TitaKita\Repository\Eloquent\OrderPaymentPlatformFeeRepository;
use TitaKita\Repository\Eloquent\OrderRefundRepository;
use TitaKita\Repository\Eloquent\OrderRepository;
use TitaKita\Repository\Eloquent\OrganizerRepository;
use TitaKita\Repository\Eloquent\OrganizerSettingsRepository;
use TitaKita\Repository\Eloquent\OutgoingMessageRepository;
use TitaKita\Repository\Eloquent\PasswordResetRepository;
use TitaKita\Repository\Eloquent\PasswordResetTokenRepository;
use TitaKita\Repository\Eloquent\ProductCategoryRepository;
use TitaKita\Repository\Eloquent\ProductPriceRepository;
use TitaKita\Repository\Eloquent\ProductRepository;
use TitaKita\Repository\Eloquent\PromoCodeRepository;
use TitaKita\Repository\Eloquent\QuestionAndAnswerViewRepository;
use TitaKita\Repository\Eloquent\ScheduleRepository;
use TitaKita\Repository\Eloquent\QuestionAnswerRepository;
use TitaKita\Repository\Eloquent\QuestionRepository;
use TitaKita\Repository\Eloquent\StripeCustomerRepository;
use TitaKita\Repository\Eloquent\StripePaymentsRepository;
use TitaKita\Repository\Eloquent\StripePayoutsRepository;
use TitaKita\Repository\Eloquent\TaxAndFeeRepository;
use TitaKita\Repository\Eloquent\TicketLookupTokenRepository;
use TitaKita\Repository\Eloquent\UserRepository;
use TitaKita\Repository\Eloquent\WaitlistEntryRepository;
use TitaKita\Repository\Eloquent\WebhookLogRepository;
use TitaKita\Repository\Eloquent\WebhookRepository;
use TitaKita\Repository\Interfaces\AccountAttributionRepositoryInterface;
use TitaKita\Repository\Interfaces\AccountConfigurationRepositoryInterface;
use TitaKita\Repository\Interfaces\AccountMessagingTierRepositoryInterface;
use TitaKita\Repository\Interfaces\AccountRepositoryInterface;
use TitaKita\Repository\Interfaces\AccountStripePlatformRepositoryInterface;
use TitaKita\Repository\Interfaces\AccountUserRepositoryInterface;
use TitaKita\Repository\Interfaces\AccountVatSettingRepositoryInterface;
use TitaKita\Repository\Interfaces\AffiliateRepositoryInterface;
use TitaKita\Repository\Interfaces\AttendeeCheckInRepositoryInterface;
use TitaKita\Repository\Interfaces\AttendeeRepositoryInterface;
use TitaKita\Repository\Interfaces\CapacityAssignmentRepositoryInterface;
use TitaKita\Repository\Interfaces\CheckInListRepositoryInterface;
use TitaKita\Repository\Interfaces\EmailTemplateRepositoryInterface;
use TitaKita\Repository\Interfaces\EventDailyStatisticRepositoryInterface;
use TitaKita\Repository\Interfaces\EventRepositoryInterface;
use TitaKita\Repository\Interfaces\EventSettingsRepositoryInterface;
use TitaKita\Repository\Interfaces\EventStatisticRepositoryInterface;
use TitaKita\Repository\Interfaces\ImageRepositoryInterface;
use TitaKita\Repository\Interfaces\InvoiceRepositoryInterface;
use TitaKita\Repository\Interfaces\MessageRepositoryInterface;
use TitaKita\Repository\Interfaces\OrderApplicationFeeRepositoryInterface;
use TitaKita\Repository\Interfaces\OrderAuditLogRepositoryInterface;
use TitaKita\Repository\Interfaces\OrderItemRepositoryInterface;
use TitaKita\Repository\Interfaces\OrderPaymentPlatformFeeRepositoryInterface;
use TitaKita\Repository\Interfaces\OrderRefundRepositoryInterface;
use TitaKita\Repository\Interfaces\OrderRepositoryInterface;
use TitaKita\Repository\Interfaces\OrganizerRepositoryInterface;
use TitaKita\Repository\Interfaces\OrganizerSettingsRepositoryInterface;
use TitaKita\Repository\Interfaces\OutgoingMessageRepositoryInterface;
use TitaKita\Repository\Interfaces\PasswordResetRepositoryInterface;
use TitaKita\Repository\Interfaces\PasswordResetTokenRepositoryInterface;
use TitaKita\Repository\Interfaces\ProductCategoryRepositoryInterface;
use TitaKita\Repository\Interfaces\ProductPriceRepositoryInterface;
use TitaKita\Repository\Interfaces\ProductRepositoryInterface;
use TitaKita\Repository\Interfaces\PromoCodeRepositoryInterface;
use TitaKita\Repository\Interfaces\QuestionAndAnswerViewRepositoryInterface;
use TitaKita\Repository\Interfaces\ScheduleRepositoryInterface;
use TitaKita\Repository\Interfaces\QuestionAnswerRepositoryInterface;
use TitaKita\Repository\Interfaces\QuestionRepositoryInterface;
use TitaKita\Repository\Interfaces\StripeCustomerRepositoryInterface;
use TitaKita\Repository\Interfaces\StripePaymentsRepositoryInterface;
use TitaKita\Repository\Interfaces\StripePayoutsRepositoryInterface;
use TitaKita\Repository\Interfaces\TaxAndFeeRepositoryInterface;
use TitaKita\Repository\Interfaces\TicketLookupTokenRepositoryInterface;
use TitaKita\Repository\Interfaces\UserRepositoryInterface;
use TitaKita\Repository\Interfaces\WaitlistEntryRepositoryInterface;
use TitaKita\Repository\Interfaces\WebhookLogRepositoryInterface;
use TitaKita\Repository\Interfaces\WebhookRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * @todo - find a way to auto-bind these
     */
    private static array $interfaceToConcreteMap = [
        UserRepositoryInterface::class => UserRepository::class,
        AccountRepositoryInterface::class => AccountRepository::class,
        AccountAttributionRepositoryInterface::class => AccountAttributionRepository::class,
        EventRepositoryInterface::class => EventRepository::class,
        ProductRepositoryInterface::class => ProductRepository::class,
        OrderRepositoryInterface::class => OrderRepository::class,
        AttendeeRepositoryInterface::class => AttendeeRepository::class,
        AffiliateRepositoryInterface::class => AffiliateRepository::class,
        OrderItemRepositoryInterface::class => OrderItemRepository::class,
        QuestionRepositoryInterface::class => QuestionRepository::class,
        QuestionAnswerRepositoryInterface::class => QuestionAnswerRepository::class,
        StripePaymentsRepositoryInterface::class => StripePaymentsRepository::class,
        PromoCodeRepositoryInterface::class => PromoCodeRepository::class,
        MessageRepositoryInterface::class => MessageRepository::class,
        PasswordResetTokenRepositoryInterface::class => PasswordResetTokenRepository::class,
        PasswordResetRepositoryInterface::class => PasswordResetRepository::class,
        TaxAndFeeRepositoryInterface::class => TaxAndFeeRepository::class,
        ImageRepositoryInterface::class => ImageRepository::class,
        ProductPriceRepositoryInterface::class => ProductPriceRepository::class,
        EventStatisticRepositoryInterface::class => EventStatisticRepository::class,
        EventDailyStatisticRepositoryInterface::class => EventDailyStatisticRepository::class,
        EventSettingsRepositoryInterface::class => EventSettingsRepository::class,
        OrganizerRepositoryInterface::class => OrganizerRepository::class,
        AccountUserRepositoryInterface::class => AccountUserRepository::class,
        CapacityAssignmentRepositoryInterface::class => CapacityAssignmentRepository::class,
        StripeCustomerRepositoryInterface::class => StripeCustomerRepository::class,
        CheckInListRepositoryInterface::class => CheckInListRepository::class,
        AttendeeCheckInRepositoryInterface::class => AttendeeCheckInRepository::class,
        ProductCategoryRepositoryInterface::class => ProductCategoryRepository::class,
        InvoiceRepositoryInterface::class => InvoiceRepository::class,
        OrderRefundRepositoryInterface::class => OrderRefundRepository::class,
        WebhookRepositoryInterface::class => WebhookRepository::class,
        WebhookLogRepositoryInterface::class => WebhookLogRepository::class,
        OrderApplicationFeeRepositoryInterface::class => OrderApplicationFeeRepository::class,
        OrderAuditLogRepositoryInterface::class => OrderAuditLogRepository::class,
        OrderPaymentPlatformFeeRepositoryInterface::class => OrderPaymentPlatformFeeRepository::class,
        StripePayoutsRepositoryInterface::class => StripePayoutsRepository::class,
        AccountConfigurationRepositoryInterface::class => AccountConfigurationRepository::class,
        QuestionAndAnswerViewRepositoryInterface::class => QuestionAndAnswerViewRepository::class,
        OutgoingMessageRepositoryInterface::class => OutgoingMessageRepository::class,
        OrganizerSettingsRepositoryInterface::class => OrganizerSettingsRepository::class,
        EmailTemplateRepositoryInterface::class => EmailTemplateRepository::class,
        AccountStripePlatformRepositoryInterface::class => AccountStripePlatformRepository::class,
        AccountVatSettingRepositoryInterface::class => AccountVatSettingRepository::class,
        TicketLookupTokenRepositoryInterface::class => TicketLookupTokenRepository::class,
        AccountMessagingTierRepositoryInterface::class => AccountMessagingTierRepository::class,
        WaitlistEntryRepositoryInterface::class => WaitlistEntryRepository::class,
        ScheduleRepositoryInterface::class => ScheduleRepository::class,
    ];

    public function register(): void
    {
        foreach (self::$interfaceToConcreteMap as $interface => $concrete) {
            $this->app->bind($interface, $concrete);
        }
    }
}
