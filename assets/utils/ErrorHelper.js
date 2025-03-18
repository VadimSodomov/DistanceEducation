export function getErrorMessage(error) {
    if (error.response && error.response.data) {
        const detail = error.response.data;
        const message = error.response.message

        if (detail) {
            const detailParts = detail.split(':');
            return detailParts.length > 1 ? detailParts[1].trim() : detail.trim();
        }

        if (message) {
            return message;
        }
    }

    return 'Произошла ошибка, попробуйте еще раз :)';
}