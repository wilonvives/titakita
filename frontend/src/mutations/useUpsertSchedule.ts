import {useMutation, useQueryClient} from "@tanstack/react-query";
import {IdParam} from "../types.ts";
import {scheduleClient, UpsertScheduleRequest} from "../api/schedule.client.ts";
import {GET_SCHEDULE_QUERY_KEY} from "../queries/useGetSchedule.ts";
import {GET_EVENT_QUERY_KEY} from "../queries/useGetEvent.ts";
import {GET_EVENT_PRODUCT_CATEGORIES_QUERY_KEY} from "../queries/useGetProductCategories.ts";

export const useUpsertSchedule = () => {
    const queryClient = useQueryClient();

    return useMutation({
        mutationFn: ({eventId, scheduleData}: {
            eventId: IdParam,
            scheduleData: UpsertScheduleRequest
        }) => scheduleClient.upsert(eventId, scheduleData),

        onSuccess: (_, variables) => {
            return Promise.all([
                queryClient.invalidateQueries({queryKey: [GET_SCHEDULE_QUERY_KEY, variables.eventId]}),
                queryClient.invalidateQueries({queryKey: [GET_EVENT_QUERY_KEY, variables.eventId]}),
                queryClient.invalidateQueries({queryKey: [GET_EVENT_PRODUCT_CATEGORIES_QUERY_KEY, variables.eventId]}),
            ]);
        }
    });
}
