<!-- @format -->
<template>
	<ClientOnly>
		<div v-if="!isLoggedIn" class="login-container">
			<div class="login-card">
				<h1 class="login-title">登入帳號</h1>

				<form @submit.prevent="handleLogin" class="login-form">
					<div class="form-group">
						<input
							type="text"
							id="login"
							v-model="loginForm.login"
							placeholder="輸入帳號或電子信箱"
							required
						/>
					</div>

					<div class="form-group">
						<input
							id="password"
							v-model="loginForm.password"
							type="password"
							placeholder="密碼"
							required
							autocomplete="current-password"
						/>
						<div class="forgot-password">
							<NuxtLink to="/auth/forgot-password"
								>忘記密碼？</NuxtLink
							>
						</div>
					</div>

					<div v-if="errorMessage" class="error-message">
						{{ errorMessage }}
					</div>

					<button
						type="submit"
						class="login-button"
						:disabled="isLoading"
					>
						<span v-if="isLoading">登入中...</span>
						<span v-else>登入</span>
					</button>
				</form>

				<div class="register-link">
					沒有帳號嗎？ <NuxtLink to="/auth/register">註冊</NuxtLink>
				</div>
			</div>
		</div>
		<template #fallback>
			<div class="loading-container">
				<div class="loading-spinner"></div>
				<p>載入中...</p>
			</div>
		</template>
	</ClientOnly>
</template>

<script setup>
import { ref, onMounted } from 'vue';

definePageMeta({
	layout: 'auth',
});

const { isLoggedIn, checkAuth, login } = useAuth();
const router = useRouter();

const loginForm = ref({
	login: '',
	password: '',
	remember: false,
});

const errorMessage = ref('');
const isLoading = ref(false);

async function handleLogin() {
	isLoading.value = true;
	errorMessage.value = '';

	try {
		const success = await login(loginForm.value);

		if (success) {
			router.push('/');
		}
	} catch (error) {
		console.error('登入失敗:', error);

		if (error.response?.status === 422) {
			errorMessage.value = '請檢查您的帳號和密碼';
		} else if (error.response?.status === 401) {
			errorMessage.value = '帳號或密碼錯誤';
		} else {
			errorMessage.value = '登入失敗，請稍後再試';
		}
	} finally {
		isLoading.value = false;
	}
}

onMounted(async () => {
	await checkAuth();

	if (isLoggedIn.value) {
		router.push('/');
	}
});
</script>

<style scoped>
@import '@/assets/css/auth/index.css';
</style>
