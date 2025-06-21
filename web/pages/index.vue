<!-- @format -->
<template>
	<ClientOnly>
		<div class="page-content">
			<div class="welcome-section">
				<h2>歡迎使用 WebHarvester</h2>
				<p>這是您的個人資料爬蟲助手，讓您輕鬆取得網頁資料</p>

				<div class="action-buttons">
					<template v-if="isLoggedIn">
						<button
							class="primary-button"
							@click="navigateTo('/scrawl')"
						>
							開始爬蟲
						</button>
						<button
							class="secondary-button"
							@click="
								navigateTo('https://www.firecrawl.dev/', {
									external: true,
								})
							"
						>
							查看教學
						</button>
					</template>
					<template v-else>
						<button
							class="primary-button"
							@click="navigateTo('/auth')"
						>
							立即登入
						</button>
						<button
							class="secondary-button"
							@click="navigateTo('/auth/register')"
						>
							建立帳號
						</button>
					</template>
				</div>
			</div>

			<div class="features-section">
				<div class="feature-card">
					<div class="feature-icon">📊</div>
					<h3>數據分析</h3>
					<p>快速分析您的爬蟲數據</p>
				</div>
				<div class="feature-card">
					<div class="feature-icon">📱</div>
					<h3>多設備支援</h3>
					<p>在任何設備上執行您的爬蟲任務</p>
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
import { onMounted } from 'vue';

const { isLoggedIn, authChecked, checkAuth } = useAuth();

onMounted(async () => {
	console.log('index 頁面載入');
	console.log(
		'初始狀態 - authChecked:',
		authChecked.value,
		'isLoggedIn:',
		isLoggedIn.value
	);

	await checkAuth();

	console.log(
		'驗證後狀態 - authChecked:',
		authChecked.value,
		'isLoggedIn:',
		isLoggedIn.value
	);
});
</script>

<style scoped>
@import '@/assets/css/index.css';
</style>
