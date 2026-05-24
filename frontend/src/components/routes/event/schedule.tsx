import {useEffect, useMemo, useState} from "react";
import {useParams} from "react-router";
import {t} from "@lingui/macro";
import {useMutation, useQueryClient} from "@tanstack/react-query";
import {
    ActionIcon,
    Alert,
    Button,
    Checkbox,
    Group,
    Modal,
    NumberInput,
    Stack,
    Text,
    Textarea,
    Tooltip,
} from "@mantine/core";
import {DatePicker, DatePickerInput} from "@mantine/dates";
import {IconCalendarPlus, IconInfoCircle, IconPencil, IconTrash} from "@tabler/icons-react";
import dayjs from "dayjs";
import {PageBody} from "../../common/PageBody";
import {PageTitle} from "../../common/PageTitle";
import {Card} from "../../common/Card";
import {HeadingWithDescription} from "../../common/Card/CardHeading";
import {ImageUploadDropzone} from "../../common/ImageUploadDropzone";
import {WheelTimePicker} from "../../common/WheelTimePicker";
import {TableSkeleton} from "../../common/TableSkeleton";
import {useGetEvent, GET_EVENT_QUERY_KEY} from "../../../queries/useGetEvent.ts";
import {useGetSchedule, GET_SCHEDULE_QUERY_KEY} from "../../../queries/useGetSchedule.ts";
import {scheduleClient} from "../../../api/schedule.client.ts";
import {showError, showSuccess} from "../../../utilites/notifications.tsx";
import {formatDate} from "../../../utilites/dates.ts";
import {BookingSession, IdParam, ImageType, Schedule as BookingScheduleData} from "../../../types.ts";
import classes from "./Schedule.module.scss";

const THREE_MONTHS_OUT = () => dayjs().add(3, "month").format("YYYY-MM-DD");

const apiError = (error: any, fallback: string): string =>
    error?.response?.data?.errors?.session?.[0]
    || error?.response?.data?.message
    || fallback;

export const Schedule = () => {
    const {eventId} = useParams();
    const queryClient = useQueryClient();
    const {data: event} = useGetEvent(eventId);
    const scheduleQuery = useGetSchedule(eventId);
    const timezone = event?.timezone ?? "UTC";

    const [durationMinutes, setDurationMinutes] = useState<number | string>(60);
    const [capacity, setCapacity] = useState<number | string>("");
    const [selectedDate, setSelectedDate] = useState<string | null>(
        dayjs().add(1, "day").format("YYYY-MM-DD"),
    );
    const [startTime, setStartTime] = useState<string>("10:00");
    const [repeatWeekly, setRepeatWeekly] = useState<boolean>(false);
    const [editing, setEditing] = useState<BookingSession | null>(null);

    useEffect(() => {
        if (scheduleQuery.isFetched && scheduleQuery.data) {
            setDurationMinutes(scheduleQuery.data.session_duration_minutes ?? 60);
            setCapacity(scheduleQuery.data.capacity_per_session ?? "");
        }
    }, [scheduleQuery.isFetched]);

    const sessions = useMemo(() => {
        return [...(scheduleQuery.data?.sessions ?? [])].sort((a, b) =>
            a.session_start_at.localeCompare(b.session_start_at),
        );
    }, [scheduleQuery.data]);

    const groupedSessions = useMemo(() => {
        const groups: { date: string; sessions: BookingSession[] }[] = [];
        sessions.forEach((session) => {
            const date = formatDate(session.session_start_at, "YYYY-MM-DD", timezone);
            const group = groups.find((g) => g.date === date);
            if (group) {
                group.sessions.push(session);
            } else {
                groups.push({date, sessions: [session]});
            }
        });
        return groups;
    }, [sessions, timezone]);

    const capacityValue = (): number | null =>
        capacity === "" || capacity === null ? null : Number(capacity);

    const onMutationSuccess = (data: BookingScheduleData) => {
        queryClient.setQueryData([GET_SCHEDULE_QUERY_KEY, eventId], data);
        queryClient.invalidateQueries({queryKey: [GET_EVENT_QUERY_KEY, eventId]});
    };

    const saveSettingsMutation = useMutation({
        mutationFn: () => scheduleClient.saveSettings(eventId, {
            session_duration_minutes: Number(durationMinutes) || 60,
            capacity_per_session: capacityValue(),
        }),
        onSuccess: (response) => onMutationSuccess(response.data),
    });

    const createMutation = useMutation({
        mutationFn: () => scheduleClient.createSessions(eventId, {
            session_date: selectedDate as string,
            start_time: startTime,
            duration_minutes: Number(durationMinutes) || 60,
            capacity: capacityValue(),
            repeat_weekly: repeatWeekly,
        }),
        onSuccess: (response) => {
            onMutationSuccess(response.data);
            showSuccess(t`Sessions added.`);
        },
        onError: (error) => showError(apiError(error, t`Could not add the sessions.`)),
    });

    const deleteMutation = useMutation({
        mutationFn: (productId: IdParam) => scheduleClient.deleteSession(eventId, productId),
        onSuccess: (response) => {
            onMutationSuccess(response.data);
            showSuccess(t`Session removed.`);
        },
        onError: (error) => showError(apiError(error, t`Could not remove the session.`)),
    });

    const updateMutation = useMutation({
        mutationFn: (payload: {
            productId: IdParam;
            session_date: string;
            start_time: string;
            duration_minutes: number;
            capacity: number | null;
            description: string | null;
        }) => scheduleClient.updateSession(eventId, payload.productId, {
            session_date: payload.session_date,
            start_time: payload.start_time,
            duration_minutes: payload.duration_minutes,
            capacity: payload.capacity,
            description: payload.description,
        }),
        onSuccess: (response) => {
            onMutationSuccess(response.data);
            setEditing(null);
            showSuccess(t`Session updated.`);
        },
        onError: (error) => showError(apiError(error, t`Could not update the session.`)),
    });

    const handleAdd = () => {
        if (!selectedDate) {
            showError(t`Please pick a date on the calendar.`);
            return;
        }
        if (!startTime) {
            showError(t`Please set a start time.`);
            return;
        }
        createMutation.mutate();
    };

    const renderCapacity = (session: BookingSession): string => {
        if (session.capacity === null) {
            return t`Unlimited`;
        }
        return t`${session.capacity_remaining ?? session.capacity}/${session.capacity} left`;
    };

    return (
        <PageBody>
            <PageTitle
                subheading={t`Pick a date, set a start time, and add it. Tick repeat to roll it out weekly. Build your sessions exactly how you want.`}
            >
                {t`Booking Schedule`}
            </PageTitle>

            <TableSkeleton numRows={5} isVisible={!event || !scheduleQuery.isFetched}/>

            {event && scheduleQuery.isFetched && (
                <>
                    <Card>
                        <HeadingWithDescription
                            heading={t`Defaults`}
                            description={t`The duration and capacity new sessions use. You can override each session later.`}
                        />
                        <div className={classes.defaultsRow}>
                            <NumberInput
                                label={t`Session duration`}
                                description={t`Minutes`}
                                value={durationMinutes}
                                onChange={setDurationMinutes}
                                onBlur={() => saveSettingsMutation.mutate()}
                                min={1}
                                max={1440}
                                step={15}
                                suffix={` ${t`minutes`}`}
                            />
                            <NumberInput
                                label={t`Capacity per session`}
                                description={t`Leave blank for unlimited`}
                                placeholder={t`Unlimited`}
                                value={capacity}
                                onChange={setCapacity}
                                onBlur={() => saveSettingsMutation.mutate()}
                                min={1}
                            />
                        </div>
                    </Card>

                    <Card>
                        <HeadingWithDescription
                            heading={t`Add sessions`}
                            description={t`Click a day, choose a start time, then add.`}
                        />
                        <div className={classes.addGrid}>
                            <DatePicker
                                value={selectedDate}
                                onChange={setSelectedDate}
                                minDate={dayjs().format("YYYY-MM-DD")}
                                maxDate={THREE_MONTHS_OUT()}
                            />
                            <Stack gap="md" className={classes.addControls}>
                                <WheelTimePicker
                                    label={t`Start time`}
                                    value={startTime}
                                    onChange={setStartTime}
                                />
                                <Checkbox
                                    label={t`Repeat weekly (for the next 3 months)`}
                                    checked={repeatWeekly}
                                    onChange={(e) => setRepeatWeekly(e.currentTarget.checked)}
                                />
                                <Button
                                    leftSection={<IconCalendarPlus size={18}/>}
                                    onClick={handleAdd}
                                    loading={createMutation.isPending}
                                >
                                    {t`Add to schedule`}
                                </Button>
                            </Stack>
                        </div>
                    </Card>

                    <Card>
                        <HeadingWithDescription
                            heading={t`Scheduled sessions`}
                            description={t`${sessions.length} session(s) scheduled.`}
                        />

                        {sessions.length === 0 && (
                            <Alert variant="light" color="gray" icon={<IconInfoCircle/>}>
                                {t`No sessions yet. Add your first one above.`}
                            </Alert>
                        )}

                        <Stack gap="lg">
                            {groupedSessions.map((group) => (
                                <div key={group.date}>
                                    <Text fw={600} mb="xs">
                                        {formatDate(group.sessions[0].session_start_at, "ddd, MMM D, YYYY", timezone)}
                                    </Text>
                                    <Stack gap="xs">
                                        {group.sessions.map((session) => (
                                            <div key={session.product_id} className={classes.sessionRow}>
                                                {session.image?.url && (
                                                    <img
                                                        src={session.image.url}
                                                        alt=""
                                                        className={classes.sessionThumb}
                                                    />
                                                )}
                                                <div className={classes.sessionTime}>
                                                    {formatDate(session.session_start_at, "HH:mm", timezone)}
                                                    {"–"}
                                                    {formatDate(session.session_end_at, "HH:mm", timezone)}
                                                </div>
                                                <div className={classes.sessionMeta}>
                                                    {session.duration_minutes
                                                        ? t`${session.duration_minutes} min`
                                                        : ""}
                                                </div>
                                                <div className={classes.sessionMeta}>
                                                    {renderCapacity(session)}
                                                </div>
                                                {session.description && (
                                                    <div className={classes.sessionDesc} title={session.description}>
                                                        {session.description}
                                                    </div>
                                                )}
                                                <Group gap={4} className={classes.sessionActions}>
                                                    <Tooltip label={t`Edit`} withArrow>
                                                        <ActionIcon
                                                            variant="subtle"
                                                            color="gray"
                                                            onClick={() => setEditing(session)}
                                                            aria-label={t`Edit session`}
                                                        >
                                                            <IconPencil size={18}/>
                                                        </ActionIcon>
                                                    </Tooltip>
                                                    <Tooltip label={t`Remove`} withArrow>
                                                        <ActionIcon
                                                            variant="subtle"
                                                            color="red"
                                                            loading={deleteMutation.isPending && deleteMutation.variables === session.product_id}
                                                            onClick={() => deleteMutation.mutate(session.product_id)}
                                                            aria-label={t`Remove session`}
                                                        >
                                                            <IconTrash size={18}/>
                                                        </ActionIcon>
                                                    </Tooltip>
                                                </Group>
                                            </div>
                                        ))}
                                    </Stack>
                                </div>
                            ))}
                        </Stack>
                    </Card>
                </>
            )}

            <EditSessionModal
                session={editing}
                timezone={timezone}
                fallbackDuration={Number(durationMinutes) || 60}
                isSaving={updateMutation.isPending}
                onClose={() => setEditing(null)}
                onSave={(payload) => updateMutation.mutate(payload)}
                onImageChanged={() => queryClient.invalidateQueries({queryKey: [GET_SCHEDULE_QUERY_KEY, eventId]})}
            />
        </PageBody>
    );
};

interface EditSessionModalProps {
    session: BookingSession | null;
    timezone: string;
    fallbackDuration: number;
    isSaving: boolean;
    onClose: () => void;
    onSave: (payload: {
        productId: IdParam;
        session_date: string;
        start_time: string;
        duration_minutes: number;
        capacity: number | null;
        description: string | null;
    }) => void;
    onImageChanged: () => void;
}

const EditSessionModal = ({session, timezone, fallbackDuration, isSaving, onClose, onSave, onImageChanged}: EditSessionModalProps) => {
    const [date, setDate] = useState<string | null>(null);
    const [time, setTime] = useState<string>("10:00");
    const [duration, setDuration] = useState<number | string>(fallbackDuration);
    const [capacity, setCapacity] = useState<number | string>("");
    const [description, setDescription] = useState<string>("");

    useEffect(() => {
        if (session) {
            setDate(formatDate(session.session_start_at, "YYYY-MM-DD", timezone));
            setTime(formatDate(session.session_start_at, "HH:mm", timezone));
            setDuration(session.duration_minutes ?? fallbackDuration);
            setCapacity(session.capacity ?? "");
            setDescription(session.description ?? "");
        }
    }, [session]);

    return (
        <Modal opened={!!session} onClose={onClose} title={t`Edit session`} centered>
            <Stack gap="md">
                <DatePickerInput
                    label={t`Date`}
                    value={date}
                    onChange={setDate}
                    valueFormat="YYYY-MM-DD"
                    minDate={dayjs().format("YYYY-MM-DD")}
                    maxDate={THREE_MONTHS_OUT()}
                />
                <WheelTimePicker
                    label={t`Start time`}
                    value={time}
                    onChange={setTime}
                />
                <NumberInput
                    label={t`Duration`}
                    value={duration}
                    onChange={setDuration}
                    min={1}
                    max={1440}
                    step={15}
                    suffix={` ${t`minutes`}`}
                />
                <NumberInput
                    label={t`Capacity`}
                    placeholder={t`Unlimited`}
                    value={capacity}
                    onChange={setCapacity}
                    min={1}
                />
                <Textarea
                    label={t`Description`}
                    placeholder={t`Optional notes shown to customers for this session`}
                    value={description}
                    onChange={(e) => setDescription(e.currentTarget.value)}
                    autosize
                    minRows={2}
                />
                {session && (
                    <div>
                        <Text fw={500} size="sm" mb={4}>{t`Image`}</Text>
                        <ImageUploadDropzone
                            imageType={"PRODUCT_IMAGE" as ImageType}
                            entityId={session.product_id}
                            existingImageData={session.image?.url
                                ? {url: session.image.url, id: session.image.id}
                                : undefined}
                            onUploadSuccess={onImageChanged}
                            onDeleteSuccess={onImageChanged}
                            displayMode="compact"
                        />
                    </div>
                )}
                <Group justify="flex-end">
                    <Button variant="default" onClick={onClose}>{t`Cancel`}</Button>
                    <Button
                        loading={isSaving}
                        onClick={() => {
                            if (!session || !date) {
                                return;
                            }
                            onSave({
                                productId: session.product_id,
                                session_date: date,
                                start_time: time,
                                duration_minutes: Number(duration) || fallbackDuration,
                                capacity: capacity === "" || capacity === null ? null : Number(capacity),
                                description: description.trim() === "" ? null : description,
                            });
                        }}
                    >
                        {t`Save`}
                    </Button>
                </Group>
            </Stack>
        </Modal>
    );
};

export default Schedule;
