<?php

declare(strict_types=1);

namespace TitaKita\Validators;

use TitaKita\DomainObjects\Enums\QuestionBelongsTo;
use TitaKita\DomainObjects\EventSettingDomainObject;
use TitaKita\DomainObjects\Generated\ProductDomainObjectAbstract;
use TitaKita\DomainObjects\Generated\QuestionDomainObjectAbstract;
use TitaKita\DomainObjects\ProductDomainObject;
use TitaKita\DomainObjects\ProductPriceDomainObject;
use TitaKita\DomainObjects\QuestionDomainObject;
use TitaKita\Repository\Eloquent\Value\Relationship;
use TitaKita\Repository\Interfaces\EventSettingsRepositoryInterface;
use TitaKita\Repository\Interfaces\ProductRepositoryInterface;
use TitaKita\Repository\Interfaces\QuestionRepositoryInterface;
use TitaKita\Validators\Rules\OrderQuestionRule;
use TitaKita\Validators\Rules\ProductQuestionRule;
use Illuminate\Routing\Route;

class CompleteOrderValidator extends BaseValidator
{
    public function __construct(
        private readonly QuestionRepositoryInterface      $questionRepository,
        private readonly ProductRepositoryInterface       $productRepository,
        private readonly EventSettingsRepositoryInterface $eventSettingsRepository,
        private readonly Route                            $route
    )
    {
    }

    public function rules(): array
    {
        $questions = $this->questionRepository
            ->loadRelation(
                new Relationship(ProductDomainObject::class, [
                    new Relationship(ProductPriceDomainObject::class)
                ])
            )
            ->findWhere(
                [QuestionDomainObjectAbstract::EVENT_ID => $this->route->parameter('event_id')]
            );

        $orderQuestions = $questions->filter(
            fn(QuestionDomainObject $question) => $question->getBelongsTo() === QuestionBelongsTo::ORDER->name
        );

        $productQuestions = $questions->filter(
            fn(QuestionDomainObject $question) => $question->getBelongsTo() === QuestionBelongsTo::PRODUCT->name
        );

        $products = $this->productRepository
            ->loadRelation(ProductPriceDomainObject::class)
            ->findWhere(
                [ProductDomainObjectAbstract::EVENT_ID => $this->route->parameter('event_id')]
            );

        /** @var EventSettingDomainObject $eventSettings */
        $eventSettings = $this->eventSettingsRepository->findFirstWhere([
            'event_id' => $this->route->parameter('event_id'),
        ]);

        $addressRules = $eventSettings->getRequireBillingAddress() ? [
            'order.address' => 'array',
            'order.address.address_line_1' => 'required|string|max:255',
            'order.address.address_line_2' => 'nullable|string|max:255',
            'order.address.city' => 'required|string|max:85',
            'order.address.state_or_region' => 'nullable|string|max:85',
            'order.address.zip_or_postal_code' => 'nullable|string|max:85',
            'order.address.country' => 'required|string|max:2',
        ] : [];

        return [
            'order.first_name' => ['required', 'string', 'max:40'],
            'order.last_name' => ['required', 'string', 'max:40'],
            'order.questions' => new OrderQuestionRule($orderQuestions, $products),
            'order.email' => 'required|email',
            'order.email_confirmation' => 'required|email|same:order.email',
            'products' => new ProductQuestionRule(
                $productQuestions,
                $products,
                $eventSettings->getAttendeeDetailsCollectionMethod(),
            ),
            ...$addressRules
        ];
    }

    public function messages(): array
    {
        return [
            'order.first_name.max' => 'First name must be under 40 characters',
            'order.last_name.max' => 'Last name must be under 40 characters',
            'order.first_name.required' => __('First name is required'),
            'order.last_name.required' => __('Last name is required'),
            'order.email' => __('A valid email is required'),
            'order.email_confirmation.required' => __('Please confirm your email address'),
            'order.email_confirmation.same' => __('Email addresses do not match'),
            'order.address.address_line_1.required' => __('Address line 1 is required'),
            'order.address.city.required' => __('City is required'),
            'order.address.zip_or_postal_code.required' => __('Zip or postal code is required'),
            'order.address.country.required' => __('Country is required'),
        ];
    }
}
