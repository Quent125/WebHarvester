<!-- @format -->
<template>
	<div class="success-container" v-if="isValidToken">
		<div class="success-card">
			<div class="success-icon">
				<svg
					xmlns="http://www.w3.org/2000/svg"
					width="64"
					height="64"
					viewBox="0 0 24 24"
					fill="none"
					stroke="currentColor"
					stroke-width="2"
					stroke-linecap="round"
					stroke-linejoin="round"
				>
					<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
					<polyline points="22 4 12 14.01 9 11.01"></polyline>
				</svg>
			</div>

			<h1 class="success-title">註冊成功！</h1>

			<p class="success-message">
				系統將在
				<span class="countdown">{{ countdown }}</span>
				秒後跳轉到登入頁面。
			</p>

			<div class="buttons">
				<button @click="goToLogin" class="go-login-button">
					立即前往登入
				</button>
			</div>
		</div>
	</div>
	<!-- 驗證中 -->
	<div class="loading-container" v-else>
		<div class="loading-spinner"></div>
		<p>驗證中...</p>
	</div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';

definePageMeta({
	layout: 'auth',
});

const { goToErrorPage } = useAuth();

const router = useRouter();
const route = useRoute();
const config = useRuntimeConfig();
const countdown = ref(5);
const isValidToken = ref(false);
const isUndefinedToken = ref(false);
let timer;

async function verifyToken() {
	const token = route.query.token;

	if (!token) {
		goToErrorPage();
		return;
	}

	try {
		const response = await $fetch(
			`${config.public.apiBase}/verify-registration-token`,
			{
				method: 'POST',
				body: { token },
			}
		);

		isValidToken.value = response.valid;
		isUndefinedToken.value = true;

		if (isValidToken.value) {
			startCountdown();
		}
	} catch (error) {
		console.error('令牌驗證失敗:', error);
		goToErrorPage();
	}
}

function startCountdown() {
	timer = setInterval(() => {
		countdown.value--;

		if (countdown.value <= 0) {
			clearInterval(timer);
			goToLogin();
		}
	}, 1000);
}

function goToLogin() {
	clearInterval(timer);
	router.push('/auth');
}

onMounted(() => {
	verifyToken();
});

onBeforeUnmount(() => {
	clearInterval(timer);
});
</script>

<style scoped>
@import '@/assets/css/auth/register-success.css';
</style>
