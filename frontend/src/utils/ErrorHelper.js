export function getErrorMessage(error, defaultMessage=null) {
    const errorData = error?.response?.data;
    defaultMessage ??= 'Произошла ошибка'

    if (!errorData) return defaultMessage;

    if (errorData.error) {
        return errorData.error;
    }

    if (errorData.status === 422 && errorData?.violations?.[0]?.title) {
        return errorData.violations[0].title;
    }

    console.log(errorData);

    return defaultMessage;
}