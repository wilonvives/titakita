import {api} from "./client";
import {GenericDataResponse, IdParam, Schedule} from "../types";

export interface UpsertScheduleRequest {
    session_duration_minutes: number;
    start_times: string[];
    capacity_per_session?: number | null;
    scope_type: Schedule['scope_type'];
    weekdays?: number[] | null;
    range_start_date?: string | null;
    range_end_date?: string | null;
    specific_dates?: string[] | null;
}

export const scheduleClient = {
    get: async (eventId: IdParam) => {
        const response = await api.get<GenericDataResponse<Schedule | null>>(
            `events/${eventId}/schedule`
        );
        return response.data;
    },
    upsert: async (eventId: IdParam, schedule: UpsertScheduleRequest) => {
        const response = await api.post<GenericDataResponse<Schedule>>(
            `events/${eventId}/schedule`, schedule
        );
        return response.data;
    },
}
