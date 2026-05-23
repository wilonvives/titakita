import {useQuery} from "@tanstack/react-query";
import {AxiosError} from "axios";
import {bookingClientPublic, BookingSessionDateGroup} from "../api/booking.client.ts";
import {IdParam} from "../types.ts";

export const GET_BOOKING_SESSIONS_QUERY_KEY = 'getBookingSessions';

export const useGetBookingSessions = (eventId: IdParam, enabled = true) => {
    return useQuery<BookingSessionDateGroup[], AxiosError>({
        queryKey: [GET_BOOKING_SESSIONS_QUERY_KEY, eventId],

        queryFn: async () => {
            const {data} = await bookingClientPublic.getSessions(eventId);
            return data;
        },

        enabled,
    });
};
