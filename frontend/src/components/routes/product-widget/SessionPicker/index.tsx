import {t} from "@lingui/macro";
import {Button, Loader, Modal, NumberInput} from "@mantine/core";
import {useNavigate, useParams} from "react-router";
import {useMutation, useQueryClient} from "@tanstack/react-query";
import {notifications} from "@mantine/notifications";
import React, {useMemo, useState} from "react";
import classNames from "classnames";
import {orderClientPublic, ProductFormPayload} from "../../../../api/order.client.ts";
import {BookingSession} from "../../../../api/booking.client.ts";
import {useGetBookingSessions} from "../../../../queries/useGetBookingSessions.ts";
import {Event} from "../../../../types.ts";
import {formatDate} from "../../../../utilites/dates.ts";
import {getSessionIdentifier} from "../../../../utilites/sessionIdentifier.ts";
import {showInfo} from "../../../../utilites/notifications.tsx";
import {PoweredByFooter} from "../../../common/PoweredByFooter";
import classes from "./SessionPicker.module.scss";
import "../../../../styles/widget/default.scss";

interface SessionPickerProps {
    event: Event;
    colors?: {
        primary?: string;
        primaryText?: string;
        secondary?: string;
        secondaryText?: string;
        background?: string;
        bodyBackground?: string;
    };
    padding?: string;
    continueButtonText?: string;
    widgetMode?: 'preview' | 'normal' | 'embedded';
    showPoweredBy?: boolean;
}

const SessionPicker = (props: SessionPickerProps) => {
    const {eventId} = useParams();
    const queryClient = useQueryClient();
    const navigate = useNavigate();
    const timezone = props.event.timezone;

    const sessionsQuery = useGetBookingSessions(eventId);
    const dateGroups = sessionsQuery.data ?? [];

    const [selectedDate, setSelectedDate] = useState<string | null>(null);
    const [selectedSession, setSelectedSession] = useState<BookingSession | null>(null);
    const [partySize, setPartySize] = useState<number>(1);
    const [orderInProcessOverlayVisible, setOrderInProcessOverlayVisible] = useState(false);

    const activeDate = selectedDate ?? dateGroups[0]?.date ?? null;

    const sessionsForDate = useMemo(() => {
        return dateGroups.find((group) => group.date === activeDate)?.sessions ?? [];
    }, [dateGroups, activeDate]);

    const maxPartySize = selectedSession?.capacity_remaining ?? 25;

    const orderMutation = useMutation({
        mutationFn: (orderData: ProductFormPayload) => orderClientPublic.create(Number(eventId), orderData),

        onSuccess: (data) => queryClient.invalidateQueries()
            .then(() => {
                const url = '/checkout/' + eventId + '/' + data.data.short_id + '/details';
                if (props.widgetMode === 'embedded') {
                    window.open(
                        url + '?session_identifier=' + data.data.session_identifier + '&utm_source=embedded_widget',
                        '_blank'
                    );
                    setOrderInProcessOverlayVisible(true);
                    return;
                }

                return navigate(url);
            }),

        onError: (error: any) => {
            notifications.show({
                message: error?.response?.data?.errors?.products?.[0]
                    || error?.response?.data?.message
                    || t`Unable to create booking. Please try again.`,
                color: 'red',
            });
        }
    });

    const handleDateSelect = (date: string) => {
        setSelectedDate(date);
        setSelectedSession(null);
        setPartySize(1);
    };

    const handleSessionSelect = (session: BookingSession) => {
        if (session.is_sold_out) {
            return;
        }
        setSelectedSession(session);
        setPartySize(1);
    };

    const handleProceed = () => {
        if (!selectedSession) {
            showInfo(t`Please select a session`);
            return;
        }

        if (props.widgetMode === 'preview') {
            return;
        }

        const payload: ProductFormPayload = {
            products: [
                {
                    product_id: selectedSession.product_id,
                    quantities: [
                        {
                            quantity: partySize,
                            price_id: selectedSession.product_price_id,
                        },
                    ],
                },
            ],
            promo_code: null,
            session_identifier: getSessionIdentifier(),
        };

        orderMutation.mutate(payload);
    };

    const renderCapacity = (session: BookingSession) => {
        if (session.is_sold_out) {
            return t`Sold out`;
        }
        if (session.capacity_remaining === null) {
            return t`Available`;
        }
        return t`${session.capacity_remaining} left`;
    };

    const noSessions = sessionsQuery.isFetched && dateGroups.length === 0;

    return (
        <div className={'hi-product-widget-container'}
             style={{
                 '--widget-background-color': props.colors?.background,
                 '--widget-primary-color': props.colors?.primary,
                 '--widget-primary-text-color': props.colors?.primaryText,
                 '--widget-secondary-color': props.colors?.secondary,
                 '--widget-secondary-text-color': props.colors?.secondaryText,
                 '--widget-padding': props?.padding,
             } as React.CSSProperties}>

            {orderInProcessOverlayVisible && (
                <Modal
                    withCloseButton={false}
                    opened={true}
                    onClose={() => setOrderInProcessOverlayVisible(false)}
                    styles={{
                        content: {borderRadius: '8px', backgroundColor: props.colors?.background || 'white'},
                        body: {padding: '30px 24px'},
                    }}
                >
                    <div style={{textAlign: 'center'}}>
                        <h3 style={{margin: '0 0 12px 0'}}>{t`Please continue in the new tab`}</h3>
                        <p style={{margin: '0 0 20px 0'}}>
                            {t`If a new tab did not open automatically, please click the button below to continue to checkout.`}
                        </p>
                        <Button
                            component="a"
                            href={'/checkout/' + eventId + '/' + orderMutation.data?.data.short_id + '/details' + '?session_identifier=' + orderMutation.data?.data.session_identifier}
                            target={'_blank'}
                            rel={'noopener noreferrer'}
                            fullWidth
                            size="md"
                        >
                            {t`Continue to Checkout`}
                        </Button>
                    </div>
                </Modal>
            )}

            {!sessionsQuery.isFetched && (
                <div style={{display: 'flex', justifyContent: 'center', padding: '2rem 0'}}>
                    <Loader size="md" type="dots"/>
                </div>
            )}

            {noSessions && (
                <div className={'hi-no-products'}>
                    <p className={'hi-no-products-message'}>
                        {t`There are no sessions available for booking`}
                    </p>
                </div>
            )}

            {sessionsQuery.isFetched && dateGroups.length > 0 && (
                <div className={classes.container}>
                    <div>
                        <div className={classes.stepLabel}>{t`Select a date`}</div>
                        <div className={classes.dateList}>
                            {dateGroups.map((group) => {
                                const isSelected = group.date === activeDate;
                                return (
                                    <button
                                        type="button"
                                        key={group.date}
                                        className={classNames(classes.dateButton, {
                                            [classes.dateButtonSelected]: isSelected,
                                        })}
                                        onClick={() => handleDateSelect(group.date)}
                                    >
                                        <span className={classes.dateButtonWeekday}>
                                            {formatDate(group.date, 'ddd', timezone)}
                                        </span>
                                        <span className={classes.dateButtonDay}>
                                            {formatDate(group.date, 'D', timezone)}
                                        </span>
                                        <span className={classes.dateButtonMonth}>
                                            {formatDate(group.date, 'MMM', timezone)}
                                        </span>
                                    </button>
                                );
                            })}
                        </div>
                    </div>

                    <div>
                        <div className={classes.stepLabel}>{t`Select a session`}</div>
                        <div className={classes.sessionList}>
                            {sessionsForDate.map((session) => {
                                const isSelected = selectedSession?.product_id === session.product_id;
                                return (
                                    <button
                                        type="button"
                                        key={session.product_id}
                                        disabled={session.is_sold_out}
                                        className={classNames(classes.sessionButton, {
                                            [classes.sessionButtonSelected]: isSelected,
                                        })}
                                        onClick={() => handleSessionSelect(session)}
                                    >
                                        <span className={classes.sessionTime}>
                                            {formatDate(session.session_start_at, 'HH:mm', timezone)}
                                            {'–'}
                                            {formatDate(session.session_end_at, 'HH:mm', timezone)}
                                        </span>
                                        <span className={classes.sessionCapacity}>
                                            {renderCapacity(session)}
                                        </span>
                                    </button>
                                );
                            })}
                        </div>
                    </div>

                    {selectedSession && (
                        <div>
                            <div className={classes.stepLabel}>{t`Party size`}</div>
                            <NumberInput
                                className={classes.partySize}
                                value={partySize}
                                onChange={(value) => setPartySize(Math.max(1, Number(value) || 1))}
                                min={1}
                                max={selectedSession.capacity_remaining ?? undefined}
                                clampBehavior="strict"
                                allowDecimal={false}
                                aria-label={t`Party size`}
                            />
                        </div>
                    )}

                    <div className={classes.footer}>
                        <Button
                            fullWidth
                            className={'hi-continue-button'}
                            onClick={handleProceed}
                            disabled={
                                !selectedSession
                                || orderMutation.isPending
                                || props.widgetMode === 'preview'
                                || partySize < 1
                                || partySize > maxPartySize
                            }
                            loading={orderMutation.isPending}
                        >
                            {props.continueButtonText || props.event?.settings?.continue_button_text || t`Continue`}
                        </Button>
                    </div>
                </div>
            )}

            {
                /**
                 * (c) Hi.Events Ltd 2025
                 *
                 * PLEASE NOTE:
                 *
                 * Hi.Events is licensed under the GNU Affero General Public License (AGPL) version 3.
                 *
                 * You can find the full license text at: https://github.com/HiEventsDev/hi.events/blob/main/LICENCE
                 *
                 * In accordance with Section 7(b) of the AGPL, we ask that you retain the "Powered by Hi.Events" notice.
                 *
                 * If you wish to remove this notice, a commercial license is available at: https://hi.events/licensing
                 */
            }
            {(props.showPoweredBy ?? true) && (
                <PoweredByFooter style={{
                    'color': props.colors?.primaryText || '#000',
                }}/>
            )}
        </div>
    );
};

export default SessionPicker;
