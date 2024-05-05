import ToastEventBus from "primevue/toasteventbus";

export default {
    showInfoToast(title, text, duration = 3600) {
        ToastEventBus.emit("add", {
            severity: "info",
            summary: title,
            detail: text,
            life: duration,
        });
    },

    showErrorToast(title, text, duration = 3600) {
        ToastEventBus.emit("add", {
            severity: "error",
            summary: title,
            detail: text,
            life: duration,
        });
    },

    showWarnToast(title, text, duration = 3600) {
        ToastEventBus.emit("add", {
            severity: "warn",
            summary: title,
            detail: text,
            life: duration,
        });
    },

    showSuccessToast(title, text, duration = 3600) {
        ToastEventBus.emit("add", {
            severity: "success",
            summary: title,
            detail: text,
            life: duration,
        });
    },

    showSecondaryToast(title, text, duration = 3600) {
        ToastEventBus.emit("add", {
            severity: "secondary",
            summary: title,
            detail: text,
            life: duration,
        });
    },

    showContrastToast(title, text, duration = 3600) {
        ToastEventBus.emit("add", {
            severity: "contrast",
            summary: title,
            detail: text,
            life: duration,
        });
    },
};
