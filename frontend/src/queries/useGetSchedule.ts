import {useQuery} from "@tanstack/react-query";
import {AxiosError} from "axios";
import {scheduleClient} from "../api/schedule.client.ts";
import {IdParam, Schedule} from "../types.ts";

export const GET_SCHEDULE_QUERY_KEY = 'getSchedule';

export const useGetSchedule = (eventId: IdParam) => {
    return useQuery<Schedule | null, AxiosError>({
        queryKey: [GET_SCHEDULE_QUERY_KEY, eventId],

        queryFn: async () => {
            const {data} = await scheduleClient.get(eventId);
            return data;
        },
    });
};
