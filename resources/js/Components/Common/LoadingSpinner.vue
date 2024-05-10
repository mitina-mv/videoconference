<template>
    <div class="loading-spinner">
        <div class="spinner"></div>
        <p class="load-title">Загрузка...</p>
    </div>
</template>

<style scoped>
.loading-spinner {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100%;
    flex-direction: column;
	gap: 1em;
}

.spinner {
    border: 4px solid rgba(0, 0, 0, 0.1);
    border-left-color: var(--primary-color);
    border-radius: 50%;
    width: 50px;
    height: 50px;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}
.load-title {
	color: var(--gray-400);
	font-weight: bolder;
}
</style>
