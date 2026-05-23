<?php

namespace TitaKita\Repository\Eloquent;

use TitaKita\DomainObjects\AttendeeCheckInDomainObject;
use TitaKita\DomainObjects\AttendeeDomainObject;
use TitaKita\DomainObjects\Generated\AttendeeDomainObjectAbstract;
use TitaKita\DomainObjects\Status\AttendeeStatus;
use TitaKita\DomainObjects\Status\OrderStatus;
use TitaKita\Http\DTO\QueryParamsDTO;
use TitaKita\Models\Attendee;
use TitaKita\Repository\Eloquent\Value\Relationship;
use TitaKita\Repository\Interfaces\AttendeeRepositoryInterface;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * @extends BaseRepository<AttendeeDomainObject>
 */
class AttendeeRepository extends BaseRepository implements AttendeeRepositoryInterface
{
    protected function getModel(): string
    {
        return Attendee::class;
    }

    public function getDomainObject(): string
    {
        return AttendeeDomainObject::class;
    }

    public function findByEventIdForExport(int $eventId): Collection
    {
        $this->applyConditions([
            'attendees.event_id' => $eventId,
        ]);

        $this->model->select('attendees.*');
        $this->model->join('orders', 'orders.id', '=', 'attendees.order_id');
        $this->model->whereIn('orders.status', [
            OrderStatus::AWAITING_OFFLINE_PAYMENT->name,
            OrderStatus::COMPLETED->name,
            OrderStatus::CANCELLED->name
        ]);

        $model = $this->model->limit(10000)->get();
        $this->resetModel();

        return $this->handleResults($model);
    }


    public function findByEventId(int $eventId, QueryParamsDTO $params): LengthAwarePaginator
    {
        $where = [
            ['attendees.event_id', '=', $eventId]
        ];

        if ($params->query) {
            $where[] = static function (Builder $builder) use ($params) {
                $builder
                    ->where(
                        DB::raw(
                            sprintf(
                                "(%s||' '||%s)",
                                'attendees.' . AttendeeDomainObjectAbstract::FIRST_NAME,
                                'attendees.' . AttendeeDomainObjectAbstract::LAST_NAME,
                            )
                        ), 'ilike', '%' . $params->query . '%')
                    ->orWhere('attendees.' . AttendeeDomainObjectAbstract::LAST_NAME, 'ilike', '%' . $params->query . '%')
                    ->orWhere('attendees.' . AttendeeDomainObjectAbstract::FIRST_NAME, 'ilike', '%' . $params->query . '%')
                    ->orWhere('attendees.' . AttendeeDomainObjectAbstract::PUBLIC_ID, 'ilike', '%' . $params->query . '%')
                    ->orWhere('attendees.' . AttendeeDomainObjectAbstract::EMAIL, 'ilike', '%' . $params->query . '%');
            };
        }

        $this->model = $this->model->select('attendees.*')
            ->join('orders', 'orders.id', '=', 'attendees.order_id')
            ->whereIn('orders.status', [OrderStatus::COMPLETED->name, OrderStatus::CANCELLED->name, OrderStatus::AWAITING_OFFLINE_PAYMENT->name]);

        if ($params->filter_fields && $params->filter_fields->isNotEmpty()) {
            $this->applyFilterFields($params, AttendeeDomainObject::getAllowedFilterFields(), prefix: 'attendees');
        }

        $sortBy = $this->validateSortColumn($params->sort_by, AttendeeDomainObject::class);
        $sortDirection = $this->validateSortDirection($params->sort_direction, AttendeeDomainObject::class);

        if ($sortBy === AttendeeDomainObject::TICKET_NAME_SORT_KEY) {
            $this->model = $this->model
                ->leftJoin('products', 'products.id', '=', 'attendees.product_id')
                ->orderBy('products.title', $sortDirection);
        } else {
            $this->model = $this->model->orderBy('attendees.' . $sortBy, $sortDirection);
        }

        return $this->paginateWhere(
            where: $where,
            limit: $params->per_page,
            page: $params->page,
        );
    }

    public function getAttendeesByCheckInShortId(string $shortId, QueryParamsDTO $params): Paginator
    {
        $where = [];
        if ($params->query) {
            $where[] = static function (Builder $builder) use ($params) {
                $builder
                    ->where(
                        DB::raw(
                            sprintf(
                                "(%s||' '||%s)",
                                'attendees.' . AttendeeDomainObjectAbstract::FIRST_NAME,
                                'attendees.' . AttendeeDomainObjectAbstract::LAST_NAME,
                            )
                        ), 'ilike', '%' . $params->query . '%')
                    ->orWhere('attendees.' . AttendeeDomainObjectAbstract::LAST_NAME, 'ilike', '%' . $params->query . '%')
                    ->orWhere('attendees.' . AttendeeDomainObjectAbstract::FIRST_NAME, 'ilike', '%' . $params->query . '%')
                    ->orWhere('attendees.' . AttendeeDomainObjectAbstract::PUBLIC_ID, 'ilike', '%' . $params->query . '%')
                    ->orWhere('attendees.' . AttendeeDomainObjectAbstract::EMAIL, 'ilike', '%' . $params->query . '%');
            };
        }

        $this->model = $this->model->select('attendees.*')
            ->join('orders', 'orders.id', '=', 'attendees.order_id')
            ->join('product_check_in_lists', 'product_check_in_lists.product_id', '=', 'attendees.product_id')
            ->join('check_in_lists', 'check_in_lists.id', '=', 'product_check_in_lists.check_in_list_id')
            ->where('check_in_lists.short_id', $shortId)
            ->whereIn('attendees.status',[AttendeeStatus::ACTIVE->name, AttendeeStatus::CANCELLED->name, AttendeeStatus::AWAITING_PAYMENT->name])
            ->whereIn('orders.status', [OrderStatus::COMPLETED->name, OrderStatus::AWAITING_OFFLINE_PAYMENT->name]);

        $this->loadRelation(new Relationship(AttendeeCheckInDomainObject::class, name: 'check_ins'));

        return $this->simplePaginateWhere(
            where: $where,
            limit: min($params->per_page, 250),
        );
    }
}
