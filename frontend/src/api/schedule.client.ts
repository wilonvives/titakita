import {api} from "./client";
import {GenericDataResponse, IdParam, Schedule} from "../types";

export interface SaveBookingSettingsRequest {
    session_duration_minutes: number;
    capacity_per_session?: number | null;
    default_price?: number;
}

export interface CreateBookingSessionsRequest {
    session_date: string;
    start_time: string;
    duration_minutes: number;
    capacity?: number | null;
    description?: string | null;
    repeat_weekly?: boolean;
    price?: number;
}

export interface UpdateBookingSessionRequest {
    session_date: string;
    start_time: string;
    duration_minutes: number;
    capacity?: number | null;
    description?: string | null;
    price?: number;
}

export const scheduleClient = {
    get: async (eventId: IdParam) => {
        const response = await api.get<GenericDataResponse<Schedule>>(
            `events/${eventId}/schedule`
        );
        return response.data;
    },
    saveSettings: async (eventId: IdParam, settings: SaveBookingSettingsRequest) => {
        const response = await api.post<GenericDataResponse<Schedule>>(
            `events/${eventId}/booking-settings`, settings
        );
        return response.data;
    },
    createSessions: async (eventId: IdParam, sessions: CreateBookingSessionsRequest) => {
        const response = await api.post<GenericDataResponse<Schedule>>(
            `events/${eventId}/sessions`, sessions
        );
        return response.data;
    },
    updateSession: async (eventId: IdParam, productId: IdParam, session: UpdateBookingSessionRequest) => {
        const response = await api.put<GenericDataResponse<Schedule>>(
            `events/${eventId}/sessions/${productId}`, session
        );
        return response.data;
    },
    deleteSession: async (eventId: IdParam, productId: IdParam) => {
        const response = await api.delete<GenericDataResponse<Schedule>>(
            `events/${eventId}/sessions/${productId}`
        );
        return response.data;
    },
}
