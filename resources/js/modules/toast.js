import PrimeVueToastService from 'primevue/toastservice';

export function addToast(title, msg = `Ошибка при отправке запроса, попробуйте позже.`, status = "error") {
    PrimeVueToastService.add({
        severity: status,
        summary: title,
        detail: msg,
        life: 3000,
        position: "bottom-right",
    });
}