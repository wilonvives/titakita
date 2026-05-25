import {publicApi} from "./public-client.ts";
import {GenericDataResponse, IdParam} from "../types.ts";

export interface BookingSession {
    product_id: number;
    product_price_id: number;
    session_start_at: string;
    session_end_at: string;
    price?: number | null;
    capacity_remaining: number | null;
    is_sold_out: boolean;
    description?: string | null;
    image_url?: string | null;
}

export interface BookingSessionDateGroup {
    date: string;
    sessions: BookingSession[];
}

export const bookingClientPublic = {
    getSessions: async (eventId: IdParam) => {
        const response = await publicApi.get<GenericDataResponse<BookingSessionDateGroup[]>>(
            `events/${eventId}/sessions`
        );
        return response.data;
    },
}
