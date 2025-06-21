<!-- @format -->
<template>
	<div class="register-container">
		<div class="register-card">
			<h1 class="register-title">註冊帳號</h1>
			<form @submit.prevent="handleRegister" class="register-form">
				<div class="form-group">
					<input
						id="email"
						v-model="email"
						type="email"
						placeholder="電子郵件"
						required
						autocomplete="email"
						@blur="checkEmail"
					/>
					<span v-if="errors.email" class="error-text">{{
						errors.email
					}}</span>
				</div>

				<div class="form-group">
					<input
						id="name"
						v-model="name"
						type="text"
						placeholder="帳號"
						required
						autocomplete="name"
						@blur="checkUsername"
					/>
					<span v-if="errors.name" class="error-text">{{
						errors.name
					}}</span>
				</div>

				<div class="form-group">
					<input
						id="password"
						v-model="password"
						type="password"
						placeholder="密碼"
						required
						@blur="checkPassword"
					/>
					<span v-if="errors.password" class="error-text">{{
						errors.password
					}}</span>
				</div>

				<div class="form-group">
					<input
						id="password_confirmation"
						v-model="passwordConfirmation"
						type="password"
						placeholder="確認密碼"
						required
					/>
				</div>

				<div class="form-group terms">
					<label for="terms"
						>使用我們服務的用戶可能上傳了您的資訊到資料庫。
						註冊即表示你同意我們的<a href="/privacy" target="_blank"
							>隱私政策</a
						>和<a href="/terms" target="_blank">服務條款</a></label
					>
					<span v-if="errors.terms" class="error-text">{{
						errors.terms
					}}</span>
				</div>

				<div v-if="generalError" class="error-message">
					{{ generalError }}
				</div>

				<button
					type="submit"
					class="register-button"
					:disabled="isLoading"
				>
					<span v-if="isLoading">註冊中...</span>
					<span v-else>註冊</span>
				</button>
			</form>

			<div class="login-link">
				已有帳號？ <NuxtLink to="/auth">立即登入</NuxtLink>
			</div>
		</div>
	</div>
</template>

<script setup>
import { ref, reactive } from 'vue';

definePageMeta({
	layout: 'auth',
});

const router = useRouter();
const config = useRuntimeConfig();

const name = ref('');
const email = ref('');
const password = ref('');
const passwordConfirmation = ref('');
const isLoading = ref(false);
const generalError = ref('');
const errors = reactive({
	name: '',
	email: '',
	password: '',
});

async function handleRegister() {
	isLoading.value = true;

	try {
		const response = await $fetch(`${config.public.apiBase}/register`, {
			method: 'POST',
			body: {
				name: name.value,
				email: email.value,
				password: password.value,
				password_confirmation: passwordConfirmation.value,
			},
		});

		const registrationToken = response.registration_token;

		router.push(`/auth/register-success?token=${registrationToken}`);
	} catch (error) {
		console.error('註冊失敗:', error);

		if (error.response) {
			console.log('錯誤詳情:', error.response);
			generalError.value = `伺服器錯誤 (${
				error.response.status
			}): ${JSON.stringify(error.response.data)}`;
		} else {
			generalError.value = `錯誤: ${error.message}`;
		}

		if (error.response?.data?.errors) {
			const serverErrors = error.response.data.errors;

			if (serverErrors.name) errors.name = serverErrors.name[0];
			if (serverErrors.email) errors.email = serverErrors.email[0];
			if (serverErrors.password)
				errors.password = serverErrors.password[0];
		} else if (error.response?.data?.message) {
			generalError.value = error.response.data.message;
		} else {
			generalError.value = '註冊過程中發生錯誤，請稍後再試';
		}
	} finally {
		isLoading.value = false;
	}
}

async function checkUsername() {
	errors.name = '';
	if (name.value) {
		try {
			const response = await $fetch(
				`${config.public.apiBase}/checkUsername`,
				{
					method: 'POST',
					body: { name: name.value },
				}
			);
			if (response.valid) {
				errors.name = '請輸入有效的信箱';
			} else if (response.exists) {
				errors.name = '此用戶名已被註冊';
			} else {
				errors.name = '';
			}
		} catch (error) {
			console.error('檢查用戶名時發生錯誤:', error);
		}
	}
}

async function checkEmail() {
	errors.email = '';
	if (email.value) {
		try {
			const response = await $fetch(
				`${config.public.apiBase}/checkEmail`,
				{
					method: 'POST',
					body: { email: email.value },
				}
			);

			if (response.exists) {
				errors.email = '此信箱已被註冊';
			} else {
				errors.email = '';
			}
		} catch (error) {
			console.error('檢查用戶名時發生錯誤:', error);
		}
	}
}

async function checkPassword() {
	errors.password = '';
	if (password.value) {
		try {
			const response = await $fetch(
				`${config.public.apiBase}/checkPassword`,
				{
					method: 'POST',
					body: { password: password.value },
				}
			);
		} catch (error) {
			errors.password = '密碼必須至少 8 個字符';
			console.error('檢查密碼時發生錯誤:', error.response._data.message);
		}
	}
}
</script>

<style scoped>
@import '@/assets/css/auth/register.css';
</style>
