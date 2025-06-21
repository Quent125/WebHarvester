<!-- @format -->
<template>
	<div class="forgot-password-container">
		<div class="forgot-password-card">
			<h1 class="forgot-password-title">無法登入嗎？</h1>
			<p class="forgot-password-description">
				輸入你的電子郵件，我們將傳送重設密碼的連結給你。
			</p>

			<form @submit.prevent="handleSubmit" class="forgot-password-form">
				<div class="form-group">
					<input
						id="email"
						v-model="email"
						type="email"
						placeholder="電子郵件"
						required
						autocomplete="email"
					/>
					<span v-if="errorMessage" class="error-text">{{
						errorMessage
					}}</span>
				</div>

				<button
					type="submit"
					class="forgot-password-button"
					:disabled="isLoading"
				>
					<span v-if="isLoading">傳送中...</span>
					<span v-else>傳送重設密碼信件</span>
				</button>
			</form>

			<div class="divider-container">
				<div class="divider-line"></div>
				<div class="divider-text">或</div>
				<div class="divider-line"></div>
			</div>

			<div class="register-link">
				<NuxtLink to="/auth/register">建立新帳號</NuxtLink>
			</div>
		</div>
	</div>
</template>

<script setup>
import { ref } from 'vue';

definePageMeta({
	layout: 'auth',
});

const router = useRouter();
const config = useRuntimeConfig();

const email = ref('');
const isLoading = ref(false);
const errorMessage = ref('');

function validateEmail() {
	const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
	if (!email.value || !emailRegex.test(email.value)) {
		errorMessage.value = '請輸入有效的電子郵件';
		return false;
	}
	return true;
}

async function handleSubmit() {
	if (!validateEmail()) return;

	errorMessage.value = '';
	isLoading.value = true;

	try {
		await $fetch(`${config.public.apiBase}/forgot-password`, {
			method: 'POST',
			body: { email: email.value },
		});

		// 成功送出重設密碼請求，導向確認頁面
		router.push('/auth/password-reset-sent');
	} catch (error) {
		console.error('重設密碼請求失敗:', error);

		if (error.response?.data?.message) {
			errorMessage.value = error.response.data.message;
		} else {
			errorMessage.value = '發送重設密碼電子郵件時發生錯誤，請稍後再試';
		}
	} finally {
		isLoading.value = false;
	}
}
</script>

<style scoped>
@import '@/assets/css/auth/forgot-password.css';
</style>
