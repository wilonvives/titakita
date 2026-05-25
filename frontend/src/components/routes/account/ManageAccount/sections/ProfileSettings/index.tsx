import {useForm, UseFormReturnType} from "@mantine/form";
import {useGetMe} from "../../../../../../queries/useGetMe.ts";
import {Alert, Button, Checkbox, NativeSelect, Select, TextInput} from "@mantine/core";
import classes from "../../ManageAccount.module.scss";
import {useEffect, useState} from "react";
import {IconInfoCircle, IconMail, IconUser, IconWorld} from "@tabler/icons-react";
import {timezones} from "../../../../../../../data/timezones.ts";
import {useUpdateMe} from "../../../../../../mutations/useUpdateMe.ts";
import {showError, showSuccess} from "../../../../../../utilites/notifications.tsx";
import {UserMeRequest} from "../../../../../../api/user.client.ts";
import {useCancelEmailChange} from "../../../../../../mutations/useCancelEmailChange.ts";
import {useFormErrorResponseHandler} from "../../../../../../hooks/useFormErrorResponseHandler.tsx";
import {t, Trans} from "@lingui/macro";
import {useResendEmailConfirmation} from "../../../../../../mutations/useResendEmailConfirmation.ts";
import {localeToFlagEmojiMap, localeToNameMap, SupportedLocales} from "../../../../../../locales.ts";
import {Fieldset} from "../../../../../common/Fieldset";
import {InputGroup} from "../../../../../common/InputGroup";
import {HeadingCard} from "../../../../../common/HeadingCard";
import {Card} from "../../../../../common/Card";
import {getConfig} from "../../../../../../utilites/config.ts";

const localeSelectData = Object.keys(localeToNameMap).map(locale => ({
    value: locale,
    label: `${localeToFlagEmojiMap[locale as SupportedLocales]} ${localeToNameMap[locale as SupportedLocales]}`,
}));

const fieldsetLegend = (icon: React.ReactNode, label: string) => (
    <span style={{display: 'flex', alignItems: 'center', gap: 8}}>
        {icon}
        {label}
    </span>
);

const ProfileSettings = () => {
    const {data: me, isFetching} = useGetMe();
    const mutation = useUpdateMe();
    const cancelEmailChangeMutation = useCancelEmailChange();
    const resendEmailConfirmationMutation = useResendEmailConfirmation();
    const errorHandler = useFormErrorResponseHandler();
    const [emailConfirmationResent, setEmailConfirmationResent] = useState(false);

    const profileForm = useForm({
        initialValues: {
            first_name: me?.first_name,
            last_name: me?.last_name,
            email: me?.email,
            timezone: me?.timezone,
            locale: me?.locale,
            marketing_opt_in: me?.marketing_opted_in_at !== null,
        },
    });

    useEffect(() => {
        profileForm.setValues({
            first_name: me?.first_name,
            last_name: me?.last_name,
            email: me?.email,
            timezone: me?.timezone,
            locale: me?.locale,
            marketing_opt_in: me?.marketing_opted_in_at !== null,
        });
    }, [me]);

    const handleSubmit = (formValues: Partial<UserMeRequest>, form: UseFormReturnType<any>) => {
        mutation.mutate({
            userData: formValues,
        }, {
            onSuccess: () => {
                showSuccess(t`Profile updated successfully`);
                document.cookie = `locale=${formValues.locale};path=/;max-age=31536000`;

                if (form.isDirty('locale')) {
                    window.location.reload();
                }
            },
            onError: (error: any) => {
                errorHandler(form, error);
            }
        });
    }

    const handleCancelEmailChange = () => {
        cancelEmailChangeMutation.mutate({
            userId: me?.id
        }, {
            onSuccess: () => showSuccess(t`Email change cancelled successfully`),
            onError: () => showError(t`Something went wrong. Please try again.`),
        });
    }

    const handleEmailConfirmationResend = () => {
        resendEmailConfirmationMutation.mutate({
            userId: me?.id
        }, {
            onSuccess: () => {
                showSuccess(t`Email confirmation resent successfully`);
                setEmailConfirmationResent(true);
            },
            onError: () => showError(t`Something went wrong. Please try again.`),
        });
    }

    return (
        <>
            <HeadingCard
                heading={t`Profile`}
                subHeading={t`Manage your personal information and preferences`}
            />
            <Card className={classes.tabContent}>
                {me?.has_pending_email_change && (
                    <Alert variant="light" color="blue" mb="md"
                           title={t`Email change pending`} icon={<IconInfoCircle/>}>
                        <p>
                            <Trans>Your email request change to <b>{me?.pending_email}</b> is pending.
                                Please check your email to confirm</Trans>
                        </p>
                        <p>
                            {t`If you did not request this change, please immediately change your password.`}
                        </p>
                        <Button onClick={handleCancelEmailChange} size={'xs'}>
                            {t`Cancel email change`}
                        </Button>
                    </Alert>
                )}
                <form onSubmit={profileForm.onSubmit((values) => handleSubmit(values, profileForm))}>
                    <fieldset disabled={isFetching}>
                        <Fieldset legend={fieldsetLegend(<IconUser size={16}/>, t`Personal Information`)}>
                            <InputGroup>
                                <TextInput required {...profileForm.getInputProps('first_name')}
                                           label={t`First Name`}/>
                                <TextInput required {...profileForm.getInputProps('last_name')}
                                           label={t`Last Name`}/>
                            </InputGroup>
                            <TextInput required {...profileForm.getInputProps('email')} label={t`Email`}/>
                            {(me && !me.is_email_verified && !emailConfirmationResent) && (
                                <Alert variant="light" mt={10}
                                       title={t`Email not verified`} icon={<IconInfoCircle/>}>
                                    <p>{t`Please verify your email address to access all features`}</p>
                                    <Button size={'xs'} onClick={handleEmailConfirmationResend}>
                                        {resendEmailConfirmationMutation.isPending ? t`Resending...` : t`Resend email confirmation`}
                                    </Button>
                                </Alert>
                            )}
                            {emailConfirmationResent && (
                                <Alert variant="light" mt={10} color="green"
                                       title={t`Email confirmation resent`} icon={<IconInfoCircle/>}>
                                    <p>{t`Please check your email to confirm your email address`}</p>
                                </Alert>
                            )}
                        </Fieldset>

                        <Fieldset mt={20} legend={fieldsetLegend(<IconWorld size={16}/>, t`Regional Settings`)}>
                            <InputGroup>
                                <Select
                                    required
                                    searchable
                                    data={timezones}
                                    {...profileForm.getInputProps('timezone')}
                                    label={t`Timezone`}
                                    placeholder={t`UTC`}
                                />
                                <NativeSelect
                                    required
                                    data={localeSelectData}
                                    value={profileForm.values.locale || ''}
                                    onChange={(e) => profileForm.setFieldValue('locale', e.target.value as SupportedLocales)}
                                    label={t`Language`}
                                />
                            </InputGroup>
                        </Fieldset>

                        <Fieldset mt={20} legend={fieldsetLegend(<IconMail size={16}/>, t`Communication Preferences`)}>
                            <Checkbox
                                {...profileForm.getInputProps('marketing_opt_in', {type: 'checkbox'})}
                                label={<Trans>Receive product updates from {getConfig("VITE_APP_NAME", "TitaKita")}.</Trans>}
                            />
                        </Fieldset>

                        <div className={classes.footer}>
                            <Button fullWidth loading={mutation.isPending} type={'submit'}>{t`Update profile`}</Button>
                        </div>
                    </fieldset>
                </form>
            </Card>
        </>
    );
}

export default ProfileSettings;
