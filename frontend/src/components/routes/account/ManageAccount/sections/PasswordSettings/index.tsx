import {useForm} from "@mantine/form";
import {Button, PasswordInput} from "@mantine/core";
import classes from "../../ManageAccount.module.scss";
import {useGetMe} from "../../../../../../queries/useGetMe.ts";
import {useUpdateMe} from "../../../../../../mutations/useUpdateMe.ts";
import {showSuccess} from "../../../../../../utilites/notifications.tsx";
import {useFormErrorResponseHandler} from "../../../../../../hooks/useFormErrorResponseHandler.tsx";
import {t} from "@lingui/macro";
import {HeadingCard} from "../../../../../common/HeadingCard";
import {Card} from "../../../../../common/Card";

const PasswordSettings = () => {
    const {isFetching} = useGetMe();
    const mutation = useUpdateMe();
    const errorHandler = useFormErrorResponseHandler();

    const passwordForm = useForm({
        initialValues: {
            current_password: '',
            password: '',
            password_confirmation: '',
        }
    });

    const handleSubmit = (formValues: typeof passwordForm.values) => {
        mutation.mutate({
            userData: formValues,
        }, {
            onSuccess: () => {
                passwordForm.reset();
                showSuccess(t`Password updated successfully`);
            },
            onError: (error: any) => {
                errorHandler(passwordForm, error);
            }
        });
    }

    return (
        <>
            <HeadingCard
                heading={t`Password`}
                subHeading={t`Change your account password`}
            />
            <Card className={classes.tabContent}>
                <form onSubmit={passwordForm.onSubmit(handleSubmit)}>
                    <fieldset disabled={isFetching}>
                        <PasswordInput
                            required
                            {...passwordForm.getInputProps('current_password')}
                            label={t`Current Password`}/>
                        <PasswordInput
                            required
                            {...passwordForm.getInputProps('password')}
                            label={t`New Password`}/>
                        <PasswordInput
                            required
                            {...passwordForm.getInputProps('password_confirmation')}
                            label={t`Confirm New Password`}/>
                        <div className={classes.footer}>
                            <Button fullWidth loading={mutation.isPending} type={'submit'}>{t`Change password`}</Button>
                        </div>
                    </fieldset>
                </form>
            </Card>
        </>
    );
}

export default PasswordSettings;
