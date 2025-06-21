<!-- @format -->
<template>
	<div class="error-container">
		<div class="error-card">
			<div class="error-icon">!</div>
			<h1>訪問被拒絕</h1>
			<p>您沒有權限訪問此頁面或頁面已過期。</p>
			<button @click="goToLogin" class="home-button">前往登入</button>
		</div>
	</div>
</template>

<script setup>
definePageMeta({
	layout: 'auth',
});

const router = useRouter();

function goToLogin() {
	router.push('/auth');
}
</script>

<style scoped>
@import '@/assets/css/errorPage.css';
</style>
