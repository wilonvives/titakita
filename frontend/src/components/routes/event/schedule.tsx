import {useEffect, useMemo} from "react";
import {useParams} from "react-router";
import {t, Trans} from "@lingui/macro";
import {
    ActionIcon,
    Alert,
    Button,
    Checkbox,
    Group,
    NumberInput,
    Radio,
    Text,
} from "@mantine/core";
import {TimeInput, DatePickerInput} from "@mantine/dates";
import {useForm} from "@mantine/form";
import {IconCalendarTime, IconInfoCircle, IconPlus, IconTrash} from "@tabler/icons-react";
import dayjs from "dayjs";
import {PageBody} from "../../common/PageBody";
import {PageTitle} from "../../common/PageTitle";
import {Card} from "../../common/Card";
import {HeadingWithDescription} from "../../common/Card/CardHeading";
import {TableSkeleton} from "../../common/TableSkeleton";
import {useGetEvent} from "../../../queries/useGetEvent.ts";
import {useGetSchedule} from "../../../queries/useGetSchedule.ts";
import {useUpsertSchedule} from "../../../mutations/useUpsertSchedule.ts";
import {UpsertScheduleRequest} from "../../../api/schedule.client.ts";
import {useFormErrorResponseHandler} from "../../../hooks/useFormErrorResponseHandler.tsx";
import {showSuccess} from "../../../utilites/notifications.tsx";
import {ScheduleScopeType} from "../../../types.ts";
import classes from "./Schedule.module.scss";

interface ScheduleFormValues {
    session_duration_minutes: number;
    capacity_per_session: number | string;
    start_times: string[];
    scope_type: ScheduleScopeType;
    single_date: string | null;
    weekdays: string[];
    range_start_date: string | null;
    range_end_date: string | null;
    specific_dates: string[];
}

const WEEKDAY_OPTIONS = (): { value: string; label: string }[] => [
    {value: '1', label: t`Monday`},
    {value: '2', label: t`Tuesday`},
    {value: '3', label: t`Wednesday`},
    {value: '4', label: t`Thursday`},
    {value: '5', label: t`Friday`},
    {value: '6', label: t`Saturday`},
    {value: '0', label: t`Sunday`},
];

const countMatchingWeekdays = (start: string, end: string, weekdays: number[]): number => {
    const startDate = dayjs(start);
    const endDate = dayjs(end);
    if (!startDate.isValid() || !endDate.isValid() || endDate.isBefore(startDate)) {
        return 0;
    }
    let count = 0;
    let cursor = startDate;
    while (!cursor.isAfter(endDate, 'day')) {
        if (weekdays.includes(cursor.day())) {
            count++;
        }
        cursor = cursor.add(1, 'day');
    }
    return count;
};

export const Schedule = () => {
    const {eventId} = useParams();
    const {data: event} = useGetEvent(eventId);
    const scheduleQuery = useGetSchedule(eventId);
    const upsertMutation = useUpsertSchedule();
    const errorHandler = useFormErrorResponseHandler();

    const form = useForm<ScheduleFormValues>({
        initialValues: {
            session_duration_minutes: 60,
            capacity_per_session: '',
            start_times: ['10:00'],
            scope_type: 'single_day',
            single_date: dayjs().add(1, 'day').format('YYYY-MM-DD'),
            weekdays: [],
            range_start_date: dayjs().add(1, 'day').format('YYYY-MM-DD'),
            range_end_date: dayjs().add(1, 'month').format('YYYY-MM-DD'),
            specific_dates: [],
        },
        validate: {
            session_duration_minutes: (value) =>
                !value || value < 1 ? t`Session duration must be at least 1 minute` : null,
            start_times: (value) =>
                value.filter((time) => !!time).length === 0
                    ? t`Add at least one start time`
                    : null,
        },
    });

    useEffect(() => {
        if (scheduleQuery.isFetched && scheduleQuery.data) {
            const s = scheduleQuery.data;
            form.setValues({
                session_duration_minutes: s.session_duration_minutes,
                capacity_per_session: s.capacity_per_session ?? '',
                start_times: s.start_times?.length ? s.start_times : ['10:00'],
                scope_type: s.scope_type,
                single_date: s.scope_type === 'single_day' && s.range_start_date
                    ? dayjs(s.range_start_date).format('YYYY-MM-DD')
                    : form.values.single_date,
                weekdays: (s.weekdays ?? []).map((d) => String(d)),
                range_start_date: s.range_start_date
                    ? dayjs(s.range_start_date).format('YYYY-MM-DD')
                    : form.values.range_start_date,
                range_end_date: s.range_end_date
                    ? dayjs(s.range_end_date).format('YYYY-MM-DD')
                    : form.values.range_end_date,
                specific_dates: (s.specific_dates ?? []).map((d) => dayjs(d).format('YYYY-MM-DD')),
            });
        }
    }, [scheduleQuery.isFetched]);

    const previewCount = useMemo(() => {
        const timesCount = form.values.start_times.filter((time) => !!time).length;
        if (timesCount === 0) {
            return 0;
        }
        switch (form.values.scope_type) {
            case 'single_day':
                return form.values.single_date ? timesCount : 0;
            case 'recurring_weekly': {
                if (!form.values.range_start_date || !form.values.range_end_date) {
                    return 0;
                }
                const weekdays = form.values.weekdays.map((d) => Number(d));
                if (weekdays.length === 0) {
                    return 0;
                }
                return countMatchingWeekdays(
                    form.values.range_start_date,
                    form.values.range_end_date,
                    weekdays,
                ) * timesCount;
            }
            case 'specific_dates':
                return form.values.specific_dates.length * timesCount;
            default:
                return 0;
        }
    }, [form.values]);

    const rangeTooLong = useMemo(() => {
        if (form.values.scope_type !== 'recurring_weekly') {
            return false;
        }
        const {range_start_date, range_end_date} = form.values;
        if (!range_start_date || !range_end_date) {
            return false;
        }
        return dayjs(range_end_date).isAfter(dayjs(range_start_date).add(3, 'month'));
    }, [form.values.scope_type, form.values.range_start_date, form.values.range_end_date]);

    const addStartTime = () => {
        form.setFieldValue('start_times', [...form.values.start_times, '']);
    };

    const removeStartTime = (index: number) => {
        form.setFieldValue(
            'start_times',
            form.values.start_times.filter((_, i) => i !== index),
        );
    };

    const handleSubmit = (values: ScheduleFormValues) => {
        const startTimes = values.start_times.filter((time) => !!time);

        const payload: UpsertScheduleRequest = {
            session_duration_minutes: values.session_duration_minutes,
            start_times: startTimes,
            capacity_per_session:
                values.capacity_per_session === '' || values.capacity_per_session === null
                    ? null
                    : Number(values.capacity_per_session),
            scope_type: values.scope_type,
            weekdays: null,
            range_start_date: null,
            range_end_date: null,
            specific_dates: null,
        };

        if (values.scope_type === 'single_day') {
            payload.range_start_date = values.single_date;
        } else if (values.scope_type === 'recurring_weekly') {
            payload.weekdays = values.weekdays.map((d) => Number(d));
            payload.range_start_date = values.range_start_date;
            payload.range_end_date = values.range_end_date;
        } else if (values.scope_type === 'specific_dates') {
            payload.specific_dates = values.specific_dates;
        }

        upsertMutation.mutate({
            eventId,
            scheduleData: payload,
        }, {
            onSuccess: (response) => {
                showSuccess(
                    t`Schedule saved. ${response.data.generated_session_count ?? 0} bookable sessions were generated.`
                );
            },
            onError: (error) => {
                errorHandler(form, error);
            },
        });
    };

    return (
        <PageBody>
            <PageTitle
                subheading={t`Define your session length and times once — we'll generate bookable time slots customers can reserve.`}
            >
                {t`Booking Schedule`}
            </PageTitle>

            <TableSkeleton numRows={5} isVisible={!event || !scheduleQuery.isFetched}/>

            {event && scheduleQuery.isFetched && (
                <Card>
                    <form onSubmit={form.onSubmit(handleSubmit)}>
                        <fieldset disabled={upsertMutation.isPending} style={{border: 'none', padding: 0, margin: 0}}>
                            <HeadingWithDescription
                                heading={t`Session settings`}
                                description={t`How long each session lasts and how many people can book it.`}
                            />

                            <NumberInput
                                className={classes.field}
                                {...form.getInputProps('session_duration_minutes')}
                                label={t`Session duration`}
                                description={t`The length of a single session.`}
                                min={1}
                                max={1440}
                                step={15}
                                suffix={` ${t`minutes`}`}
                                required
                            />

                            <NumberInput
                                className={classes.field}
                                {...form.getInputProps('capacity_per_session')}
                                label={t`Capacity per session`}
                                description={t`Maximum number of people per session. Leave blank for unlimited.`}
                                placeholder={t`Unlimited`}
                                min={1}
                            />

                            <Text fw={500} size="sm" mt="md">{t`Start times`}</Text>
                            <Text c="dimmed" size="xs" mb="xs">
                                {t`Add one or more start times for each day (e.g. 10:00, 13:00, 15:30).`}
                            </Text>
                            <div className={classes.startTimesList}>
                                {form.values.start_times.map((_, index) => (
                                    <div className={classes.startTimeRow} key={index}>
                                        <TimeInput
                                            className={classes.startTimeInput}
                                            {...form.getInputProps(`start_times.${index}`)}
                                            aria-label={t`Start time`}
                                        />
                                        <ActionIcon
                                            variant="subtle"
                                            color="red"
                                            size="lg"
                                            aria-label={t`Remove start time`}
                                            disabled={form.values.start_times.length <= 1}
                                            onClick={() => removeStartTime(index)}
                                        >
                                            <IconTrash size={18}/>
                                        </ActionIcon>
                                    </div>
                                ))}
                            </div>
                            {form.errors.start_times && (
                                <Text c="red" size="xs" mt={4}>{form.errors.start_times}</Text>
                            )}
                            <Button
                                variant="light"
                                size="xs"
                                mt="xs"
                                leftSection={<IconPlus size={14}/>}
                                onClick={addStartTime}
                            >
                                {t`Add start time`}
                            </Button>

                            <div style={{marginTop: '1.5rem'}}>
                                <HeadingWithDescription
                                    heading={t`When does it run?`}
                                    description={t`Choose how the sessions repeat.`}
                                />
                            </div>

                            <Radio.Group
                                {...form.getInputProps('scope_type')}
                                className={classes.field}
                            >
                                <Group mt="xs">
                                    <Radio value="single_day" label={t`Single day`}/>
                                    <Radio value="recurring_weekly" label={t`Recurring weekly`}/>
                                    <Radio value="specific_dates" label={t`Specific dates`}/>
                                </Group>
                            </Radio.Group>

                            {form.values.scope_type === 'single_day' && (
                                <DatePickerInput
                                    className={classes.field}
                                    label={t`Date`}
                                    placeholder={t`Pick a date`}
                                    valueFormat="YYYY-MM-DD"
                                    value={form.values.single_date}
                                    onChange={(value) => form.setFieldValue('single_date', value)}
                                />
                            )}

                            {form.values.scope_type === 'recurring_weekly' && (
                                <>
                                    <Checkbox.Group
                                        label={t`Days of the week`}
                                        value={form.values.weekdays}
                                        onChange={(value) => form.setFieldValue('weekdays', value)}
                                    >
                                        <div className={classes.weekdays}>
                                            {WEEKDAY_OPTIONS().map((day) => (
                                                <Checkbox key={day.value} value={day.value} label={day.label}/>
                                            ))}
                                        </div>
                                    </Checkbox.Group>

                                    <div className={`${classes.dateRange} ${classes.field}`} style={{marginTop: '1rem'}}>
                                        <DatePickerInput
                                            label={t`Start date`}
                                            placeholder={t`Pick a date`}
                                            valueFormat="YYYY-MM-DD"
                                            value={form.values.range_start_date}
                                            onChange={(value) => form.setFieldValue('range_start_date', value)}
                                        />
                                        <DatePickerInput
                                            label={t`End date`}
                                            placeholder={t`Pick a date`}
                                            valueFormat="YYYY-MM-DD"
                                            value={form.values.range_end_date}
                                            onChange={(value) => form.setFieldValue('range_end_date', value)}
                                        />
                                    </div>
                                    <Text c="dimmed" size="xs">
                                        {t`The date range must be 3 months or less.`}
                                    </Text>
                                    {rangeTooLong && (
                                        <Text c="red" size="xs" mt={4}>
                                            {t`The date range cannot be longer than 3 months.`}
                                        </Text>
                                    )}
                                </>
                            )}

                            {form.values.scope_type === 'specific_dates' && (
                                <DatePickerInput
                                    className={classes.field}
                                    type="multiple"
                                    label={t`Dates`}
                                    placeholder={t`Pick dates`}
                                    valueFormat="YYYY-MM-DD"
                                    value={form.values.specific_dates}
                                    onChange={(value) => form.setFieldValue('specific_dates', value)}
                                />
                            )}

                            <Alert
                                className={classes.preview}
                                variant="light"
                                color="blue"
                                icon={<IconCalendarTime/>}
                                title={t`Sessions preview`}
                            >
                                <div className={classes.previewCount}>{previewCount}</div>
                                <Trans>bookable sessions will be generated.</Trans>
                            </Alert>

                            <Alert variant="light" color="gray" icon={<IconInfoCircle/>} mb="lg">
                                {t`Saving this schedule turns the event into a booking event and generates the sessions automatically. Existing booked sessions are never removed.`}
                            </Alert>

                            <Button
                                type="submit"
                                loading={upsertMutation.isPending}
                                disabled={rangeTooLong}
                                leftSection={<IconCalendarTime size={18}/>}
                            >
                                {t`Save schedule`}
                            </Button>
                        </fieldset>
                    </form>
                </Card>
            )}
        </PageBody>
    );
};

export default Schedule;
